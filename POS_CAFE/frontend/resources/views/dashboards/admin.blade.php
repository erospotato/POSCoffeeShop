<x-app-layout>
    <x-slot name="header">
        Administrator Executive Overview
    </x-slot>

    <!-- Stat KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Gross Sales -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Gross Sales</p>
                <h3 class="text-2xl font-extrabold text-slate-900">${{ number_format($financials['gross_sales'], 2) }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">{{ $financials['order_count'] }} paid transactions</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Net Sales -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Net Sales</p>
                <h3 class="text-2xl font-extrabold text-emerald-600">${{ number_format($financials['net_sales'], 2) }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">Refunds: -${{ number_format($financials['total_refunds'], 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Operating Profit -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Operating Profit</p>
                <h3 class="text-2xl font-extrabold {{ $financials['operating_profit'] >= 0 ? 'text-indigo-600' : 'text-rose-600' }}">
                    ${{ number_format($financials['operating_profit'], 2) }}
                </h3>
                <p class="text-[11px] text-slate-400 mt-1">COGS: ${{ number_format($financials['cogs'], 2) }} | Exp: ${{ number_format($financials['operating_expenses'], 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>

        <!-- Inventory Stock Value -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Total Stock Value</p>
                <h3 class="text-2xl font-extrabold text-slate-900">${{ number_format($financials['inventory_value'], 2) }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">Avg Order: ${{ number_format($financials['average_order_value'], 2) }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Sales Trend (7 Days) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="font-bold text-slate-900 text-sm">7-Day Sales Trend</h4>
                    <p class="text-xs text-slate-400">Daily gross sales revenue retrieved from database</p>
                </div>
                <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-lg">Real-time</span>
            </div>
            <div class="h-64">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <!-- Payment Methods Doughnut -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="mb-4">
                <h4 class="font-bold text-slate-900 text-sm">Sales by Payment Method</h4>
                <p class="text-xs text-slate-400">Cash, Card, QR Code distribution</p>
            </div>
            <div class="h-64 flex items-center justify-center">
                <canvas id="paymentMethodChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tables Row: Low Stock & Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Low Stock Alerts -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="font-bold text-slate-900 text-sm">Low Stock Inventory Alerts</h4>
                    <p class="text-xs text-slate-400">Ingredients currently at or below minimum threshold</p>
                </div>
                <a href="{{ route('ingredients.index') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700">View All &rarr;</a>
            </div>

            @if($lowStockIngredients->isEmpty())
                <div class="py-8 text-center text-xs text-slate-400 bg-slate-50 rounded-xl">
                    <svg class="w-8 h-8 mx-auto text-emerald-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    All ingredient stock levels are currently healthy!
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="text-slate-400 border-b border-slate-100 uppercase text-[10px]">
                            <tr>
                                <th class="pb-2">Ingredient</th>
                                <th class="pb-2">Current</th>
                                <th class="pb-2">Minimum</th>
                                <th class="pb-2 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($lowStockIngredients as $ing)
                                <tr>
                                    <td class="py-2.5 font-medium text-slate-900">{{ $ing->name }}</td>
                                    <td class="py-2.5 text-rose-600 font-bold">{{ number_format($ing->current_stock, 1) }} {{ $ing->unit }}</td>
                                    <td class="py-2.5 text-slate-500">{{ number_format($ing->min_stock_alert, 1) }} {{ $ing->unit }}</td>
                                    <td class="py-2.5 text-right">
                                        <a href="{{ route('purchase-orders.create') }}" class="text-[11px] font-semibold text-amber-600 hover:underline">Create PO</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Recent Completed / Queue Orders -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="font-bold text-slate-900 text-sm">Recent Store Orders</h4>
                    <p class="text-xs text-slate-400">Live order queue and finalized transactions</p>
                </div>
                <a href="{{ route('reports.sales') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700">All Sales &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="text-slate-400 border-b border-slate-100 uppercase text-[10px]">
                        <tr>
                            <th class="pb-2">Order #</th>
                            <th class="pb-2">Type</th>
                            <th class="pb-2">Status</th>
                            <th class="pb-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentOrders as $ord)
                            <tr>
                                <td class="py-2.5 font-semibold text-slate-900">
                                    <a href="{{ route('pos.receipt', $ord->id) }}" target="_blank" class="hover:text-amber-600 hover:underline">
                                        {{ $ord->order_number }}
                                    </a>
                                </td>
                                <td class="py-2.5 text-slate-500 capitalize">{{ str_replace('_', ' ', $ord->order_type) }}</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ord->status->badgeClass() }}">
                                        {{ $ord->status->label() }}
                                    </span>
                                </td>
                                <td class="py-2.5 text-right font-bold text-slate-900">${{ number_format($ord->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js Scripts Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sales Trend
            const trendCtx = document.getElementById('salesTrendChart')?.getContext('2d');
            if (trendCtx) {
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: @json($salesTrend['labels']),
                        datasets: [{
                            label: 'Daily Sales ($)',
                            data: @json($salesTrend['data']),
                            borderColor: '#d97706',
                            backgroundColor: 'rgba(217, 119, 6, 0.1)',
                            fill: true,
                            tension: 0.3,
                            borderWidth: 2,
                            pointRadius: 4,
                            pointBackgroundColor: '#d97706',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // Payment Methods Doughnut
            const paymentCtx = document.getElementById('paymentMethodChart')?.getContext('2d');
            if (paymentCtx) {
                new Chart(paymentCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($paymentBreakdown['labels']),
                        datasets: [{
                            data: @json($paymentBreakdown['data']),
                            backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
