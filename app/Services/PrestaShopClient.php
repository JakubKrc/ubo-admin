<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class PrestaShopClient
{
    public function __construct(
        protected string $baseUrl,
        protected string $apiKey,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            baseUrl: rtrim(config('services.prestashop.url'), '/'),
            apiKey: config('services.prestashop.key'),
        );
    }

    protected function request(): PendingRequest
    {
        return Http::withBasicAuth($this->apiKey, '')
            ->acceptJson()
            ->timeout(10);
    }

    /**
     * Fetch orders placed at or after the given date.
     *
     * @return array<int, array<string, mixed>>
     */
    public function ordersSince(CarbonInterface $since): array
    {
        $response = $this->request()
            ->get("{$this->baseUrl}/api/orders", [
                'output_format' => 'JSON',
                'display' => 'full',
                'filter[date_add]' => '>['.$since->format('Y-m-d H:i:s').']',
                'date' => 1,
                'sort' => '[date_add_DESC]',
                'limit' => 200,
            ]);

        return $response->json('orders') ?? [];
    }

    /**
     * Fetch all stock_availables records (product_id, quantity).
     *
     * @return array<int, array<string, mixed>>
     */
    public function stockLevels(): array
    {
        $response = $this->request()
            ->get("{$this->baseUrl}/api/stock_availables", [
                'output_format' => 'JSON',
                'display' => 'full',
                'limit' => 500,
            ]);

        return $response->json('stock_availables') ?? [];
    }

    /**
     * Count rows in a top-level resource (products, customers, orders, ...).
     * PrestaShop's webservice has no dedicated count endpoint, so we
     * ask for just the id field and count what we get back.
     */
    public function count(string $resource): int
    {
        $response = $this->request()
            ->get("{$this->baseUrl}/api/{$resource}", [
                'output_format' => 'JSON',
                'display' => '[id]',
                'limit' => 10000,
            ]);

        return count($response->json($resource) ?? []);
    }

    /**
     * Fetch product details for the given IDs.
     *
     * @param  array<int, int|string>  $ids
     * @return array<int, array<string, mixed>>  keyed by product id
     */
    public function products(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $response = $this->request()
            ->get("{$this->baseUrl}/api/products", [
                'output_format' => 'JSON',
                'display' => '[id,name,price,active]',
                'filter[id]' => '['.implode('|', $ids).']',
                'limit' => count($ids),
            ]);

        $products = $response->json('products') ?? [];

        return collect($products)->keyBy('id')->all();
    }
}
