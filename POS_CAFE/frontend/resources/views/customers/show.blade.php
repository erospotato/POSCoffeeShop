<x-app-layout>
    <x-slot name="header">
        Customer Details: {{ $customer->name }}
    </x-slot>

    <!-- Customer Overview Card -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Points Balance</span>
            <div class="mt-2 flex items-baseline space-x-2">
                <span class="text-3xl font-black text-amber-600">{{ $customer->points_balance }}</span>
                <span class="text-xs text-slate-500 font-bold">pts</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Available for reward redemption</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Lifetime Spending</span>
            <div class="mt-2 text-3xl font-black text-slate-900">${{ number_format($customer->total_spent, 2) }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Total revenue contributed</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Completed Orders</span>
            <div class="mt-2 text-3xl font-black text-slate-900">{{ $customer->orders->count() }}</div>
            <p class="text-[11px] text-slate-400 mt-1">All dine-in, takeaway, delivery</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex flex-col justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Contact Info</span>
                <div class="mt-2 text-xs font-bold text-slate-800">{{ $customer->phone }}</div>
                <div class="text-xs text-slate-500 truncate">{{ $customer->email ?? 'No email on file' }}</div>
            </div>
            <div class="flex items-center space-x-2 mt-3 pt-3 border-t border-slate-100">
                <a href="{{ route('customers.edit', $customer->id) }}" class="text-xs font-bold text-amber-600 hover:text-amber-700">Edit Profile &rarr;</a>
                <span class="text-slate-300">|</span>
                <a href="{{ route('loyalty.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">Redeem Rewards &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Tabs / Sections: Order History & Loyalty Ledger -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Order History -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Purchase History</h3>
                    <p class="text-xs text-slate-500">All past orders linked to this customer account</p>
                </div>
                <span class="text-xs font-bold text-slate-500">{{ $customer->orders->count() }} Orders</span>
            </div>

            <div class="overflow-x-auto max-h-96">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] border-b border-slate-200 sticky top-0">
                        <tr>
                            <th class="px-5 py-3">Order #</th>
                            <th class="px-5 py-3">Items</th>
                            <th class="px-5 py-3">Total</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($customer->orders as $order)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-mono font-bold text-slate-900">
                                    <a href="{{ route('pos.receipt', $order->id) }}" target="_blank" class="hover:text-amber-600 hover:underline">
                                        #{{ $order->order_number }}
                                    </a>
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $order->items->sum('quantity') }} items</td>
                                <td class="px-5 py-3 font-bold text-slate-900">${{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $order->payment_status instanceof \App\Enums\PaymentStatus ? $order->payment_status->badgeClass() : ($order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $order->payment_status instanceof \App\Enums\PaymentStatus ? $order->payment_status->label() : ucfirst($order->payment_status ?? '') }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-slate-400 text-[11px]">{{ $order->created_at?->format('M d, Y H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-400">No orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Loyalty Points Ledger -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Loyalty Points Activity</h3>
                    <p class="text-xs text-slate-500">Points earned, redeemed, or reversed</p>
                </div>
                <span class="text-xs font-bold text-amber-600">{{ $customer->loyaltyTransactions->count() }} Entries</span>
            </div>

            <div class="overflow-x-auto max-h-96">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] border-b border-slate-200 sticky top-0">
                        <tr>
                            <th class="px-5 py-3">Type</th>
                            <th class="px-5 py-3">Points</th>
                            <th class="px-5 py-3">Description</th>
                            <th class="px-5 py-3">Balance After</th>
                            <th class="px-5 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($customer->loyaltyTransactions as $txn)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $txn->type === 'earned' ? 'bg-emerald-100 text-emerald-800' : ($txn->type === 'redeemed' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ ucfirst($txn->type) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 font-mono font-bold {{ $txn->points > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $txn->points > 0 ? '+' . $txn->points : $txn->points }}
                                </td>
                                <td class="px-5 py-3 text-slate-700 max-w-xs truncate">
                                    @if($txn->order)
                                        <a href="{{ route('pos.receipt', $txn->order_id) }}" target="_blank" class="font-mono font-bold text-slate-900 hover:text-amber-600 hover:underline">#{{ $txn->order->order_number }}</a> —
                                    @endif
                                    {{ $txn->notes ?? '—' }}
                                </td>
                                <td class="px-5 py-3 font-bold text-slate-900">{{ $txn->balance_after }} pts</td>
                                <td class="px-5 py-3 text-slate-400 text-[11px]">{{ $txn->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-400">No loyalty transactions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
