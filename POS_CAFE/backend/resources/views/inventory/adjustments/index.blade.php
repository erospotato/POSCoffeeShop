<x-app-layout>
    <x-slot name="header">
        Stock Adjustments & Wastage
    </x-slot>

    <!-- Top Action & Form Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- New Adjustment Form -->
        <div class="lg:col-span-1 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h4 class="font-bold text-slate-900 text-sm mb-1">Record Stock Adjustment</h4>
            <p class="text-xs text-slate-500 mb-4">Log physical count corrections, spoilage, or kitchen damage with reasons.</p>

            <form method="POST" action="{{ route('adjustments.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ingredient *</label>
                    <select name="ingredient_id" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                        <option value="">Select ingredient...</option>
                        @foreach($ingredients as $ing)
                            @php
                                $cStock = (float)$ing->current_stock;
                                $displayStock = ($cStock == (int)$cStock) ? (int)$cStock : rtrim(rtrim(number_format($cStock, 2), '0'), '.');
                            @endphp
                            <option value="{{ $ing->id }}">{{ $ing->name }} (Available: {{ $displayStock }} {{ $ing->unit }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Reason / Category *</label>
                    <select name="type" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                        <option value="spoilage_waste">Expired / Spoilage Waste</option>
                        <option value="damage">Spillage / Kitchen Damage</option>
                        <option value="count_correction">Physical Audit Correction</option>
                        <option value="internal_use">Staff Training / Internal Tasting</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Quantity Change (Signed) *</label>
                    <input type="number" step="0.01" name="quantity_adjusted" placeholder="e.g. -1 for 1 bottle/gallon waste, +2 for gain" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-bold">
                    <span class="text-[10px] text-slate-400 mt-1 block">Negative value reduces stock (e.g. -1 Gallon); positive increases stock.</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Detailed Reason *</label>
                    <textarea name="reason" rows="2" placeholder="e.g. Broken bottle during restocking, milk carton expired..." required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">{{ old('reason') }}</textarea>
                </div>

                <button type="submit" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow-sm transition">
                    Submit Adjustment
                </button>
            </form>
        </div>

        <!-- Recent Adjustments Table -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="font-bold text-slate-900 text-sm">Adjustment History</h4>
                    <p class="text-xs text-slate-400">All recorded manual interventions and inventory loss logs</p>
                </div>
                <a href="{{ route('adjustments.ledger') }}" class="text-xs font-bold text-amber-600 hover:underline">
                    View Full Audit Ledger &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] border-b border-slate-100">
                        <tr>
                            <th class="pb-2">Date</th>
                            <th class="pb-2">Ingredient</th>
                            <th class="pb-2">Type</th>
                            <th class="pb-2">Qty</th>
                            <th class="pb-2">Cost Impact</th>
                            <th class="pb-2">By</th>
                            <th class="pb-2">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($adjustments as $adj)
                            <tr>
                                <td class="py-2.5 text-slate-500">{{ $adj->created_at->format('M d, H:i') }}</td>
                                <td class="py-2.5 font-bold text-slate-900">{{ $adj->ingredient->name }}</td>
                                <td class="py-2.5 capitalize text-slate-600">{{ str_replace('_', ' ', $adj->type) }}</td>
                                <td class="py-2.5 font-mono font-bold {{ $adj->quantity_adjusted < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $adj->quantity_adjusted > 0 ? '+' : '' }}{{ number_format($adj->quantity_adjusted, 1) }} {{ $adj->ingredient->unit }}
                                </td>
                                <td class="py-2.5 font-bold text-slate-900">${{ number_format($adj->cost_impact, 2) }}</td>
                                <td class="py-2.5 text-slate-600">{{ $adj->user->name }}</td>
                                <td class="py-2.5 text-slate-500 max-w-xs truncate">{{ $adj->reason }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">No adjustments logged yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($adjustments->hasPages())
                <div class="mt-4 pt-3 border-t border-slate-100">
                    {{ $adjustments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
