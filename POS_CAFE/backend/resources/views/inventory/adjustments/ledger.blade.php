<x-app-layout>
    <x-slot name="header">
        Immutable Stock Movement Ledger
    </x-slot>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('adjustments.ledger') }}" class="flex items-center space-x-3 flex-1">
            <select name="ingredient_id" onchange="this.form.submit()" class="text-xs px-3 py-2 border border-slate-300 rounded-xl">
                <option value="">All Ingredients</option>
                @foreach($ingredients as $ing)
                    <option value="{{ $ing->id }}" {{ request('ingredient_id') == $ing->id ? 'selected' : '' }}>{{ $ing->name }}</option>
                @endforeach
            </select>

            <select name="movement_type" onchange="this.form.submit()" class="text-xs px-3 py-2 border border-slate-300 rounded-xl">
                <option value="">All Movement Types</option>
                <option value="order_deduction" {{ request('movement_type') === 'order_deduction' ? 'selected' : '' }}>Order Recipe Deduction</option>
                <option value="purchase_receipt" {{ request('movement_type') === 'purchase_receipt' ? 'selected' : '' }}>Purchase Order Receipt</option>
                <option value="adjustment_increase" {{ request('movement_type') === 'adjustment_increase' ? 'selected' : '' }}>Stock Adjustment (+)</option>
                <option value="adjustment_decrease" {{ request('movement_type') === 'adjustment_decrease' ? 'selected' : '' }}>Stock Adjustment (-)</option>
                <option value="waste" {{ request('movement_type') === 'waste' ? 'selected' : '' }}>Wastage / Spoilage</option>
                <option value="refund_restock" {{ request('movement_type') === 'refund_restock' ? 'selected' : '' }}>Refund Restock</option>
            </select>

            <button type="submit" class="px-3 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold">Filter</button>
        </form>

        <a href="{{ route('adjustments.index') }}" class="text-xs font-bold text-amber-600 hover:underline">
            &larr; Back to Adjustments
        </a>
    </div>

    <!-- Ledger Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Timestamp</th>
                        <th class="px-6 py-3">Ingredient</th>
                        <th class="px-6 py-3 whitespace-nowrap">Movement Type</th>
                        <th class="px-6 py-3">Previous Stock</th>
                        <th class="px-6 py-3">Quantity Delta</th>
                        <th class="px-6 py-3">New Stock</th>
                        <th class="px-6 py-3">Staff</th>
                        <th class="px-6 py-3">Reference / Reason</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $m)
                        <tr class="hover:bg-slate-50/80 transition font-mono">
                            <td class="px-6 py-3 text-slate-500 font-sans whitespace-nowrap">{{ $m->created_at->format('M d, H:i:s') }}</td>
                            <td class="px-6 py-3 font-bold text-slate-900 font-sans">{{ $m->ingredient->name }}</td>
                            <td class="px-6 py-3 font-sans whitespace-nowrap">
                                @php
                                    $badge = match($m->movement_type) {
                                        'order_deduction' => 'bg-indigo-100 text-indigo-800',
                                        'purchase_receipt' => 'bg-emerald-100 text-emerald-800',
                                        'waste' => 'bg-rose-100 text-rose-800',
                                        default => 'bg-slate-100 text-slate-700'
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold whitespace-nowrap tracking-wide {{ $badge }}">
                                    {{ strtoupper(str_replace('_', ' ', $m->movement_type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-slate-500">{{ number_format($m->previous_stock, 1) }} {{ $m->ingredient->unit }}</td>
                            <td class="px-6 py-3 font-bold {{ $m->quantity > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $m->quantity > 0 ? '+' : '' }}{{ number_format($m->quantity, 1) }} {{ $m->ingredient->unit }}
                            </td>
                            <td class="px-6 py-3 font-black text-slate-900">{{ number_format($m->new_stock, 1) }} {{ $m->ingredient->unit }}</td>
                            <td class="px-6 py-3 font-sans text-slate-600">{{ $m->user?->name ?? 'System' }}</td>
                            <td class="px-6 py-3 font-sans text-slate-500 max-w-xs truncate">{{ $m->reason }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">No stock movement ledger records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
