<x-app-layout>
    <x-slot name="header">
        Edit Promotion: {{ $promotion->name }}
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Edit Campaign</h2>
                    <p class="text-xs text-slate-500">Update promotion details, code, or active status.</p>
                </div>
                <a href="{{ route('promotions.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                    &larr; Back to Promotions
                </a>
            </div>

            <form method="POST" action="{{ route('promotions.update', $promotion->id) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Campaign Name *</label>
                        <input type="text" name="name" value="{{ old('name', $promotion->name) }}" required
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                        @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Promo Code</label>
                        <input type="text" name="code" value="{{ old('code', $promotion->code) }}"
                               class="w-full text-xs px-3.5 py-2.5 font-mono uppercase border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                        @error('code') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Discount Type *</label>
                        <select name="type" required class="w-full text-xs px-3 py-2.5 border border-slate-300 rounded-xl">
                            <option value="percentage" {{ old('type', $promotion->type) === 'percentage' ? 'selected' : '' }}>Percentage Discount (%)</option>
                            <option value="fixed_amount" {{ old('type', $promotion->type) === 'fixed_amount' ? 'selected' : '' }}>Fixed Amount Off ($)</option>
                            <option value="combo" {{ old('type', $promotion->type) === 'combo' ? 'selected' : '' }}>Combo Deal</option>
                        </select>
                        @error('type') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Discount Value *</label>
                        <input type="number" step="0.01" name="value" value="{{ old('value', $promotion->value) }}" required
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                        @error('value') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Minimum Order Spend ($)</label>
                        <input type="number" step="0.01" name="min_spend" value="{{ old('min_spend', $promotion->min_spend) }}"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Max Usage Limit</label>
                        <input type="number" name="usage_limit" value="{{ old('usage_limit', $promotion->usage_limit) }}"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Start Date</label>
                        <input type="date" name="start_date" value="{{ old('start_date', $promotion->start_date?->format('Y-m-d')) }}"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">End Date</label>
                        <input type="date" name="end_date" value="{{ old('end_date', $promotion->end_date?->format('Y-m-d')) }}"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $promotion->is_active) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <span class="text-xs font-semibold text-slate-700">Promotion Active</span>
                    </label>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" onclick="if(confirm('Delete promotion {{ $promotion->name }}?')) document.getElementById('delete-promo-form').submit();"
                            class="px-4 py-2 text-xs font-bold text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition">
                        Delete Promotion
                    </button>

                    <div class="flex items-center space-x-3">
                        <a href="{{ route('promotions.index') }}" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50">
                            Cancel
                        </a>
                        <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-sm transition">
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>

            <form id="delete-promo-form" method="POST" action="{{ route('promotions.destroy', $promotion->id) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</x-app-layout>
