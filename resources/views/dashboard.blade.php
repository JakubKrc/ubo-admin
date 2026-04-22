<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-2">

        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-50">Sales overview</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Live data from <a href="https://shop.ubo.jkjdev.eu" target="_blank" class="underline">shop.ubo.jkjdev.eu</a>
                — fetched {{ $fetched_at->diffForHumans() }}, cached for 2 minutes.
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            @foreach ([
                ['label' => 'Products in catalog', 'value' => number_format($product_count)],
                ['label' => 'Orders placed (all time)', 'value' => number_format($order_count_total)],
                ['label' => 'Customers registered', 'value' => number_format($customer_count)],
            ] as $card)
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $card['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-50">
                        {{ $card['value'] }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            @foreach ([
                ['label' => 'Revenue today',  'value' => $revenue_today],
                ['label' => 'Revenue this week', 'value' => $revenue_week],
                ['label' => 'Revenue this month', 'value' => $revenue_month],
            ] as $card)
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $card['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-50">
                        {{ number_format($card['value'], 2) }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-4 lg:grid-cols-2">

            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                    Top products (last 30 days)
                </h2>
                @if (empty($top_products))
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">No orders yet.</p>
                @else
                    <table class="w-full text-sm">
                        <thead class="text-left text-zinc-500 dark:text-zinc-400">
                            <tr>
                                <th class="pb-2">Product</th>
                                <th class="pb-2 text-right">Sold</th>
                                <th class="pb-2 text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($top_products as $p)
                                <tr>
                                    <td class="py-2 text-zinc-900 dark:text-zinc-50">{{ $p['name'] }}</td>
                                    <td class="py-2 text-right text-zinc-700 dark:text-zinc-300">{{ $p['count'] }}</td>
                                    <td class="py-2 text-right text-zinc-900 dark:text-zinc-50">
                                        {{ number_format($p['revenue'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                    Low stock (under 10 units)
                </h2>
                @if (empty($low_stock))
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">All products well stocked.</p>
                @else
                    <table class="w-full text-sm">
                        <thead class="text-left text-zinc-500 dark:text-zinc-400">
                            <tr>
                                <th class="pb-2">Product ID</th>
                                <th class="pb-2 text-right">Quantity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($low_stock as $s)
                                <tr>
                                    <td class="py-2 text-zinc-900 dark:text-zinc-50">#{{ $s['product_id'] }}</td>
                                    <td class="py-2 text-right text-zinc-900 dark:text-zinc-50">
                                        {{ $s['quantity'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="text-xs text-zinc-400 dark:text-zinc-500">
            Total orders in last 30 days: {{ $order_count_30d }}
        </div>
    </div>
</x-layouts.app>
