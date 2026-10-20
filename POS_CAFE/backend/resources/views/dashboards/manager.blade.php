<x-app-layout>
    <x-slot name="header">
        Manager Operations Dashboard
    </x-slot>

    <!-- Stat KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Today's Sales -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Today's Sales</p>
                <h3 class="text-2xl font-extrabold text-slate-900">${{ number_format($todayFinancials['gross_sales'], 2) }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">{{ $todayFinancials['order_count'] }} orders today</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Kitchen Queue -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Active Kitchen Orders</p>
                <h3 class="text-2xl font-extrabold text-indigo-600">{{ $kitchenOrdersCount }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">Pending or preparing</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
        </div>

        <!-- Low Stock Ingredients -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Low Stock Alerts</p>
                <h3 class="text-2xl font-extrabold {{ $lowStockIngredients->count() > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                    {{ $lowStockIngredients->count() }}
                </h3>
                <p class="text-[11px] text-slate-400 mt-1">Items below safety limit</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>

        <!-- Open Shifts -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Active Shifts</p>
                <h3 class="text-2xl font-extrabold text-emerald-600">{{ $openShifts->count() }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">Cashiers currently online</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Active Cashier Shifts & Expenses Review Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Active Shifts Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="font-bold text-slate-900 text-sm">Active Cashier Shifts</h4>
                    <p class="text-xs text-slate-400">Cash drawer monitoring and discrepancies</p>
                </div>
                <a href="{{ route('shifts.index') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700">All Shifts &rarr;</a>
            </div>

            @if($openShifts->isEmpty())
                <div class="py-8 text-center text-xs text-slate-400 bg-slate-50 rounded-xl">
                    No active shifts currently open.
                </div>
            @else
                <div class="space-y-3">
                    @foreach($openShifts as $shift)
                        <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/50 flex items-center justify-between">
                            <div>
                                <div class="font-bold text-slate-900 text-xs flex items-center">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse mr-2"></span>
                                    {{ $shift->user->name }} (Shift #{{ $shift->id }})
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    Opened {{ $shift->opened_at->format('H:i') }} | Float: ${{ number_format($shift->opening_cash, 2) }}
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-extrabold text-slate-900 text-sm">${{ number_format($shift->expected_cash, 2) }}</span>
                                <div class="text-[10px] text-slate-400">Expected in Drawer</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Recent Operating Expenses -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="font-bold text-slate-900 text-sm">Recent Store Expenses</h4>
                    <p class="text-xs text-slate-400">Utilities, maintenance, and store supplies</p>
                </div>
                <a href="{{ route('expenses.index') }}" class="text-xs font-semibold text-amber-600 hover:text-amber-700">View Expenses &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="text-slate-400 border-b border-slate-100 uppercase text-[10px]">
                        <tr>
                            <th class="pb-2">Date</th>
                            <th class="pb-2">Category</th>
                            <th class="pb-2">Amount</th>
                            <th class="pb-2 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentExpenses as $exp)
                            <tr>
                                <td class="py-2.5 text-slate-500">{{ $exp->expense_date->format('M d') }}</td>
                                <td class="py-2.5 font-medium text-slate-900">{{ $exp->category->name }}</td>
                                <td class="py-2.5 font-bold text-slate-900">${{ number_format($exp->amount, 2) }}</td>
                                <td class="py-2.5 text-right">
                                    @if($exp->is_approved)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Approved</span>
                                    @else
                                        <form method="POST" action="{{ route('expenses.approve', $exp->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 hover:bg-amber-200">Approve</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
