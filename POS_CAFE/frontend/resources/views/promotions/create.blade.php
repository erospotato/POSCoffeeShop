<x-app-layout>
    <x-slot name="header">
        Create Promotion or Discount
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Campaign Configuration</h2>
                    <p class="text-xs text-slate-500">Define discount values, criteria, and optional promo codes.</p>
                </div>
                <a href="{{ route('promotions.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                    &larr; Back to Promotions
                </a>
            </div>

            <form method="POST" action="{{ route('promotions.store') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Campaign Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Happy Hour Special"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                        @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Promo Code</label>
                        <input type="text" name="code" value="{{ old('code') }}" placeholder="e.g. HAPPY10 (leave blank for automatic)"
                               class="w-full text-xs px-3.5 py-2.5 font-mono uppercase border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                        @error('code') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Discount Type *</label>
                        <select name="type" required class="w-full text-xs px-3 py-2.5 border border-slate-300 rounded-xl">
                            <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Percentage Discount (%)</option>
                            <option value="fixed_amount" {{ old('type') === 'fixed_amount' ? 'selected' : '' }}>Fixed Amount Off ($)</option>
                            <option value="combo" {{ old('type') === 'combo' ? 'selected' : '' }}>Combo Deal</option>
                        </select>
                        @error('type') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Discount Value *</label>
                        <input type="number" step="0.01" name="value" value="{{ old('value') }}" required placeholder="e.g. 10 for 10% or $10"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                        @error('value') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Minimum Order Spend ($)</label>
                        <input type="number" step="0.01" name="min_spend" value="{{ old('min_spend', 0) }}" placeholder="0.00"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Max Usage Limit</label>
                        <input type="number" name="usage_limit" value="{{ old('usage_limit') }}" placeholder="Optional limit (e.g. 100)"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Start Date</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">End Date</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <span class="text-xs font-semibold text-slate-700">Promotion Active</span>
                    </label>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('promotions.index') }}" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        Save Promotion
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
