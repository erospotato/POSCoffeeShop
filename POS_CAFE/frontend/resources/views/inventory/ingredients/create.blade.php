<x-app-layout>
    <x-slot name="header">
        Create Stock Ingredient
    </x-slot>

    <div class="max-w-2xl bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('ingredients.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ingredient Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Colombian Espresso Beans" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">SKU / Identifier *</label>
                    <input type="text" name="sku" value="{{ old('sku') }}" required placeholder="e.g. ING-COL-001" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Stock Unit *</label>
                    <select name="unit" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                        <option value="g">Grams (g)</option>
                        <option value="ml">Milliliters (ml)</option>
                        <option value="pcs">Pieces / Units (pcs)</option>
                        <option value="kg">Kilograms (kg)</option>
                        <option value="l">Liters (L)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Cost Per Unit ($) *</label>
                    <input type="number" step="0.0001" name="cost_per_unit" value="{{ old('cost_per_unit', '0.0250') }}" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Initial Stock Count</label>
                    <input type="number" step="0.001" name="current_stock" value="{{ old('current_stock', '1000.000') }}" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Low-Stock Alert Level *</label>
                    <input type="number" step="0.001" name="min_stock_alert" value="{{ old('min_stock_alert', '200.000') }}" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-bold text-rose-600">
                </div>
            </div>

            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" name="is_active" value="1" id="is_active" checked class="rounded text-amber-500 focus:ring-amber-400">
                <label for="is_active" class="text-xs font-semibold text-slate-700">Active ingredient available for recipes</label>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                <a href="{{ route('ingredients.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm">Save Ingredient</button>
            </div>
        </form>
    </div>
</x-app-layout>
