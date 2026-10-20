<x-app-layout>
    <x-slot name="header">
        Edit Recipe: {{ $recipe->product->name }} {{ $recipe->variant ? '('.$recipe->variant->name.')' : '' }}
    </x-slot>

    <div class="max-w-3xl bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('recipes.update', $recipe->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Barista Preparation Instructions</label>
                <textarea name="instructions" rows="2" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">{{ old('instructions', $recipe->instructions) }}</textarea>
            </div>

            <!-- Recipe Items -->
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h4 class="font-bold text-slate-900 text-xs">Ingredients Bill of Materials</h4>
                        <p class="text-[11px] text-slate-500">Deducted ingredients per order</p>
                    </div>
                    <button type="button" onclick="addIngredientRow()" class="text-xs font-bold text-amber-600 hover:text-amber-700">+ Add Ingredient</button>
                </div>

                <div id="recipeItemsContainer" class="space-y-2">
                    @foreach($recipe->items as $index => $item)
                        <div class="flex items-center space-x-2">
                            <select name="items[{{ $index }}][ingredient_id]" required class="flex-2 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                                @foreach($ingredients as $ing)
                                    <option value="{{ $ing->id }}" {{ $item->ingredient_id == $ing->id ? 'selected' : '' }}>
                                        {{ $ing->name }} ({{ $ing->unit }})
                                    </option>
                                @endforeach
                            </select>
                            <input type="number" step="0.001" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" required class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg font-bold">
                            <input type="text" name="items[{{ $index }}][unit]" value="{{ $item->unit }}" required class="w-24 text-xs px-3 py-1.5 border border-slate-300 rounded-lg uppercase font-semibold">
                            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 text-xs px-2 font-bold">&times;</button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a href="{{ route('recipes.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm">Update Recipe</button>
            </div>
        </form>
    </div>

    <script>
        const ingredientsData = @json($ingredients);
        let itemIndex = {{ $recipe->items->count() + 1 }};

        function addIngredientRow() {
            const container = document.getElementById('recipeItemsContainer');
            const row = document.createElement('div');
            row.className = 'flex items-center space-x-2';

            let options = '<option value="">Select ingredient...</option>';
            ingredientsData.forEach(ing => {
                options += `<option value="${ing.id}">${ing.name} (${ing.unit})</option>`;
            });

            row.innerHTML = `
                <select name="items[${itemIndex}][ingredient_id]" required class="flex-2 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                    ${options}
                </select>
                <input type="number" step="0.001" name="items[${itemIndex}][quantity]" placeholder="Quantity" value="10.0" required class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg font-bold">
                <input type="text" name="items[${itemIndex}][unit]" placeholder="Unit" value="g" required class="w-24 text-xs px-3 py-1.5 border border-slate-300 rounded-lg uppercase font-semibold">
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 text-xs px-2 font-bold">&times;</button>
            `;
            container.appendChild(row);
            itemIndex++;
        }
    </script>
</x-app-layout>
