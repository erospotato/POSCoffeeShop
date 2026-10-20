<x-app-layout>
    <x-slot name="header">
        Promotions & Discounts
    </x-slot>

    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-sm font-bold text-slate-800">Promotional Campaigns & Coupons</h2>
            <p class="text-xs text-slate-500">Configure percentage or fixed discounts, minimum spend thresholds, and promo codes.</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('promotions.create') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm transition">
                + Create Promotion
            </a>
        </div>
    </div>

    <!-- Promotions Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Campaign / Promo Name</th>
                        <th class="px-6 py-3">Code</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Discount Value</th>
                        <th class="px-6 py-3">Min Spend</th>
                        <th class="px-6 py-3">Validity Window</th>
                        <th class="px-6 py-3">Usage</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($promotions as $promo)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-3.5 font-bold text-slate-900">{{ $promo->name }}</td>
                            <td class="px-6 py-3.5">
                                @if($promo->code)
                                    <span class="px-2 py-0.5 rounded-md font-mono text-[11px] font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $promo->code }}
                                    </span>
                                @else
                                    <span class="text-slate-400">Automatic / None</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $promo->type === 'percentage' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ ucfirst(str_replace('_', ' ', $promo->type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 font-extrabold text-slate-900">
                                {{ $promo->type === 'percentage' ? number_format($promo->value, 0) . '%' : '$' . number_format($promo->value, 2) }}
                            </td>
                            <td class="px-6 py-3.5 text-slate-600">
                                {{ $promo->min_spend > 0 ? '$' . number_format($promo->min_spend, 2) : 'None' }}
                            </td>
                            <td class="px-6 py-3.5 text-slate-500 text-[11px]">
                                @if($promo->start_date && $promo->end_date)
                                    {{ \Carbon\Carbon::parse($promo->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($promo->end_date)->format('M d, Y') }}
                                @else
                                    Always Active
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-slate-600 font-medium">
                                {{ $promo->used_count ?? 0 }} / {{ $promo->usage_limit ?? '∞' }}
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $promo->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $promo->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right space-x-2">
                                <a href="{{ route('promotions.edit', $promo->id) }}" class="font-bold text-slate-600 hover:text-amber-600">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-400">No promotions or discounts created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($promotions->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $promotions->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
