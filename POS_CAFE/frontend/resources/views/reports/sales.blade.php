<x-app-layout>
    <x-slot name="header">
        Sales & Financial Analytics
    </x-slot>

    <!-- Filter & Export Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <form method="GET" action="{{ route('reports.sales') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center space-x-2 bg-white px-3 py-1.5 rounded-xl border border-slate-300 shadow-xs">
                <span class="text-xs font-bold text-slate-500 uppercase">From</span>
                <input type="date" name="start_date" value="{{ $startDate }}" class="text-xs border-0 p-0 focus:ring-0 text-slate-800 font-semibold">
                <span class="text-xs font-bold text-slate-500 uppercase">To</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="text-xs border-0 p-0 focus:ring-0 text-slate-800 font-semibold">
            </div>

            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-xs transition">
                Apply Date Filter
            </button>
        </form>

        <div class="flex items-center space-x-2">
            <a href="{{ route('reports.export_csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-sm flex items-center space-x-1 transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </a>
        </div>
    </div>

    <!-- Financial KPI Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Gross Sales</span>
            <span class="text-lg font-black text-slate-900 mt-1 block">${{ number_format($financials['gross_sales'], 2) }}</span>
            <span class="text-[10px] text-slate-500">{{ $financials['order_count'] }} total orders</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Refunds</span>
            <span class="text-lg font-black text-rose-600 mt-1 block">-${{ number_format($financials['total_refunds'], 2) }}</span>
            <span class="text-[10px] text-slate-500">Authorized refunds</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Net Sales</span>
            <span class="text-lg font-black text-indigo-700 mt-1 block">${{ number_format($financials['net_sales'], 2) }}</span>
            <span class="text-[10px] text-slate-500">Gross minus refunds</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">COGS (Stock Cost)</span>
            <span class="text-lg font-black text-amber-700 mt-1 block">${{ number_format($financials['cogs'], 2) }}</span>
            <span class="text-[10px] text-slate-500">Ingredients consumed</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Operating Expenses</span>
            <span class="text-lg font-black text-rose-700 mt-1 block">${{ number_format($financials['operating_expenses'], 2) }}</span>
            <span class="text-[10px] text-slate-500">Approved expenses</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Operating Profit</span>
            <span class="text-lg font-black {{ $financials['operating_profit'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }} mt-1 block">
                ${{ number_format($financials['operating_profit'], 2) }}
            </span>
            <span class="text-[10px] text-slate-500">Net Sales - COGS - Exp</span>
        </div>
    </div>

    <!-- Secondary Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase">Gross Profit</span>
                <div class="text-xl font-black text-slate-800">${{ number_format($financials['gross_profit'], 2) }}</div>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-semibold text-emerald-600">
                    {{ $financials['net_sales'] > 0 ? number_format(($financials['gross_profit'] / $financials['net_sales']) * 100, 1) : 0 }}% Margin
                </span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase">Average Order Value (AOV)</span>
                <div class="text-xl font-black text-slate-800">${{ number_format($financials['average_order_value'], 2) }}</div>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-slate-500">{{ $financials['order_count'] }} paid transactions</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase">Current Inventory Stock Value</span>
                <div class="text-xl font-black text-slate-800">${{ number_format($financials['inventory_value'], 2) }}</div>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-slate-500">Asset value on hand</span>
            </div>
        </div>
    </div>

    <!-- Chart.js Visualizations -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Sales Trend Line Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-4">Daily Sales Revenue (Last 30 Days)</h3>
            <div class="h-64">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <!-- Payment Method Breakdown Doughnut -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-4">Sales by Payment Method</h3>
            <div class="h-64 flex items-center justify-center">
                <canvas id="paymentMethodChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Breakdown Tables: Cashier & Top Products -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales by Cashier -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Performance by Cashier</h3>
                <span class="text-xs text-slate-400">Selected period</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Cashier</th>
                            <th class="px-6 py-3">Orders Processed</th>
                            <th class="px-6 py-3 text-right">Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($salesByCashier as $c)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3.5 font-bold text-slate-900">{{ $c['cashier_name'] }}</td>
                                <td class="px-6 py-3.5 text-slate-600">{{ $c['orders_count'] }} orders</td>
                                <td class="px-6 py-3.5 text-right font-black text-slate-900">${{ number_format($c['total_sales'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-slate-400">No cashier sales recorded in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Selling Products -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Top 10 Best-Selling Menu Items</h3>
                <span class="text-xs text-slate-400">All-time volume</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Product Name</th>
                            <th class="px-6 py-3">Units Sold</th>
                            <th class="px-6 py-3 text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($topProducts as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3.5 font-bold text-slate-900">{{ $item['item_name'] }}</td>
                                <td class="px-6 py-3.5 text-slate-600 font-semibold">{{ $item['total_qty'] }} units</td>
                                <td class="px-6 py-3.5 text-right font-black text-slate-900">${{ number_format($item['total_sales'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-slate-400">No menu items sold yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Trend Line Chart
            const trendCtx = document.getElementById('salesTrendChart')?.getContext('2d');
            if (trendCtx && window.Chart) {
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: @json($salesTrend['labels']),
                        datasets: [{
                            label: 'Sales Revenue ($)',
                            data: @json($salesTrend['data']),
                            borderColor: '#d97706',
                            backgroundColor: 'rgba(217, 119, 6, 0.08)',
                            borderWidth: 2.5,
                            tension: 0.3,
                            fill: true,
                            pointBackgroundColor: '#d97706',
                            pointRadius: 3,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(val) { return '$' + val; },
                                    font: { size: 10 }
                                }
                            },
                            x: {
                                ticks: { font: { size: 10 } }
                            }
                        }
                    }
                });
            }

            // Payment Doughnut Chart
            const payCtx = document.getElementById('paymentMethodChart')?.getContext('2d');
            if (payCtx && window.Chart) {
                const payLabels = @json($paymentBreakdown['labels']);
                const payData = @json($paymentBreakdown['data']);

                new Chart(payCtx, {
                    type: 'doughnut',
                    data: {
                        labels: payLabels.length > 0 ? payLabels : ['No Data'],
                        datasets: [{
                            data: payData.length > 0 ? payData : [1],
                            backgroundColor: payData.length > 0
                                ? ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899']
                                : ['#e2e8f0'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 12, font: { size: 11 } }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
