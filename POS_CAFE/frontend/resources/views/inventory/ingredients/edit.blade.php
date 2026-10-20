<x-app-layout>
    <x-slot name="header">
        Edit Ingredient: {{ $ingredient->name }}
    </x-slot>

    <div class="max-w-2xl bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('ingredients.update', $ingredient->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ingredient Name *</label>
                    <input type="text" name="name" value="{{ old('name', $ingredient->name) }}" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">SKU / Identifier *</label>
                    <input type="text" name="sku" value="{{ old('sku', $ingredient->sku) }}" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Stock Unit *</label>
                    <select name="unit" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-medium">
                        <option value="Gallon" {{ strcasecmp($ingredient->unit, 'Gallon') === 0 ? 'selected' : '' }}>Gallon (e.g. Milk, Dairy)</option>
                        <option value="Bottle" {{ strcasecmp($ingredient->unit, 'Bottle') === 0 ? 'selected' : '' }}>Bottle (e.g. Syrups, Sauces)</option>
                        <option value="Carton" {{ strcasecmp($ingredient->unit, 'Carton') === 0 ? 'selected' : '' }}>Carton (e.g. Oat Milk, Cream)</option>
                        <option value="Bag" {{ strcasecmp($ingredient->unit, 'Bag') === 0 ? 'selected' : '' }}>Bag (e.g. Coffee Beans)</option>
                        <option value="Tin" {{ strcasecmp($ingredient->unit, 'Tin') === 0 ? 'selected' : '' }}>Tin / Can (e.g. Matcha Powder, Tea)</option>
                        <option value="Box" {{ strcasecmp($ingredient->unit, 'Box') === 0 ? 'selected' : '' }}>Box (e.g. Bakery Dough, Pastries)</option>
                        <option value="pcs" {{ strcasecmp($ingredient->unit, 'pcs') === 0 ? 'selected' : '' }}>Pieces / Units (pcs)</option>
                        <option value="kg" {{ strcasecmp($ingredient->unit, 'kg') === 0 ? 'selected' : '' }}>Kilograms (kg)</option>
                        <option value="L" {{ strcasecmp($ingredient->unit, 'L') === 0 ? 'selected' : '' }}>Liters (L)</option>
                        <option value="g" {{ strcasecmp($ingredient->unit, 'g') === 0 ? 'selected' : '' }}>Grams (g)</option>
                        <option value="ml" {{ strcasecmp($ingredient->unit, 'ml') === 0 ? 'selected' : '' }}>Milliliters (ml)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Cost Per Unit ($) *</label>
                    <input type="number" step="0.0001" name="cost_per_unit" value="{{ old('cost_per_unit', $ingredient->cost_per_unit) }}" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Current Stock Count</label>
                    <input type="number" step="0.001" name="current_stock" value="{{ old('current_stock', $ingredient->current_stock) }}" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Low-Stock Alert Level *</label>
                    <input type="number" step="0.001" name="min_stock_alert" value="{{ old('min_stock_alert', $ingredient->min_stock_alert) }}" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-bold text-rose-600">
                </div>
            </div>

            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" name="is_active" value="1" id="is_active" {{ $ingredient->is_active ? 'checked' : '' }} class="rounded text-amber-500 focus:ring-amber-400">
                <label for="is_active" class="text-xs font-semibold text-slate-700">Active ingredient available for recipes</label>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                <a href="{{ route('ingredients.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm">Update Ingredient</button>
            </div>
        </form>
    </div>
</x-app-layout>
