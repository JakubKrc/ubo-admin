<?php

namespace App\Http\Controllers;

use App\Services\PrestaShopClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $data = Cache::remember('dashboard:summary', now()->addMinutes(2), function () {
            $client = PrestaShopClient::fromConfig();

            $orders = $client->ordersSince(Carbon::now()->subDays(30));

            return [
                'product_count' => $client->count('products'),
                'order_count_total' => $client->count('orders'),
                'customer_count' => $client->count('customers'),
                'revenue_today' => $this->sumOrders($orders, Carbon::today()),
                'revenue_week' => $this->sumOrders($orders, Carbon::now()->startOfWeek()),
                'revenue_month' => $this->sumOrders($orders, Carbon::now()->startOfMonth()),
                'order_count_30d' => count($orders),
                'top_products' => $this->topProducts($orders, $client, limit: 5),
                'low_stock' => $this->lowStock($client, threshold: 10),
                'fetched_at' => Carbon::now(),
            ];
        });

        return view('dashboard', $data);
    }

    protected function sumOrders(array $orders, Carbon $since): float
    {
        return collect($orders)
            ->filter(fn ($o) => Carbon::parse($o['date_add'])->gte($since))
            ->sum(fn ($o) => (float) $o['total_paid']);
    }

    /**
     * @return array<int, array{name: string, revenue: float, count: int}>
     */
    protected function topProducts(array $orders, PrestaShopClient $client, int $limit): array
    {
        $rows = collect($orders)
            ->flatMap(fn ($o) => $o['associations']['order_rows'] ?? [])
            ->groupBy('product_id')
            ->map(fn ($group, $productId) => [
                'product_id' => (int) $productId,
                'count' => $group->sum(fn ($r) => (int) $r['product_quantity']),
                'revenue' => $group->sum(fn ($r) => (float) $r['unit_price_tax_incl'] * (int) $r['product_quantity']),
            ])
            ->sortByDesc('revenue')
            ->take($limit)
            ->values();

        $productIds = $rows->pluck('product_id')->all();
        $products = $client->products($productIds);

        return $rows->map(function ($row) use ($products) {
            $name = $products[$row['product_id']]['name'] ?? "product #{$row['product_id']}";
            return [
                'name' => is_array($name) ? ($name[0]['value'] ?? json_encode($name)) : (string) $name,
                'count' => $row['count'],
                'revenue' => $row['revenue'],
            ];
        })->all();
    }

    /**
     * @return array<int, array{product_id: int, quantity: int}>
     */
    protected function lowStock(PrestaShopClient $client, int $threshold): array
    {
        return collect($client->stockLevels())
            ->filter(fn ($s) => (int) $s['quantity'] < $threshold && (int) $s['id_product_attribute'] === 0)
            ->sortBy('quantity')
            ->take(10)
            ->map(fn ($s) => [
                'product_id' => (int) $s['id_product'],
                'quantity' => (int) $s['quantity'],
            ])
            ->values()
            ->all();
    }
}
