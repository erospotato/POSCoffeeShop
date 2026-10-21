<x-app-layout>
    <x-slot name="header">
        Create Product Recipe
    </x-slot>

    <div class="max-w-3xl bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('recipes.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Target Product *</label>
                    <select name="product_id" id="productSelect" onchange="updateVariantOptions()" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                        <option value="">Select menu product...</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" data-variants="{{ json_encode($prod->variants) }}" {{ $selectedProductId == $prod->id ? 'selected' : '' }}>
                                {{ $prod->name }} ({{ $prod->category->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Variant / Size (Optional)</label>
                    <select name="product_variant_id" id="variantSelect" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                        <option value="">Default Recipe (All sizes or base)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Barista Preparation Instructions</label>
                <textarea name="instructions" rows="2" placeholder="e.g. Extract double espresso shot (36g yield), steam milk to 65°C microfoam..." class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">{{ old('instructions') }}</textarea>
            </div>

            <!-- Recipe Items (Bill of Materials) -->
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h4 class="font-bold text-slate-900 text-xs">Ingredients Bill of Materials</h4>
                        <p class="text-[11px] text-slate-500">Specify ingredient quantities deducted per order item</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="smartSuggestIngredients()" class="text-xs font-bold text-amber-800 hover:text-amber-900 bg-amber-100 hover:bg-amber-200 border border-amber-300 px-3 py-1.5 rounded-lg shadow-2xs transition flex items-center space-x-1" title="Smartly suggest ingredients based on selected product">
                            <span>💡</span>
                            <span>Smart Suggest</span>
                        </button>
                        <button type="button" onclick="addIngredientRow()" class="text-xs font-bold text-slate-700 hover:text-slate-900 bg-white border border-slate-300 px-3 py-1.5 rounded-lg shadow-2xs transition">+ Add Row</button>
                    </div>
                </div>

                <div id="recipeItemsContainer" class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <select name="items[0][ingredient_id]" required class="flex-2 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                            <option value="">Select ingredient...</option>
                            @foreach($ingredients as $ing)
                                <option value="{{ $ing->id }}">{{ $ing->name }} ({{ $ing->unit }})</option>
                            @endforeach
                        </select>
                        <input type="number" step="0.001" name="items[0][quantity]" placeholder="Quantity" value="1.0" required class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg font-bold">
                        <input type="text" name="items[0][unit]" placeholder="Unit (e.g. Gallon, Bottle, Bag)" value="{{ $ingredients->first()?->unit ?? 'pcs' }}" required class="w-28 text-xs px-3 py-1.5 border border-slate-300 rounded-lg uppercase font-semibold">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a href="{{ route('recipes.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm">Save Recipe</button>
            </div>
        </form>
    </div>

    <script>
        const ingredientsData = @json($ingredients);
        let itemIndex = 1;

        function updateVariantOptions() {
            const prodSelect = document.getElementById('productSelect');
            const varSelect = document.getElementById('variantSelect');
            varSelect.innerHTML = '<option value="">Default Recipe (All sizes or base)</option>';

            const opt = prodSelect.options[prodSelect.selectedIndex];
            if (!opt || !opt.dataset.variants) return;

            const variants = JSON.parse(opt.dataset.variants);
            variants.forEach(v => {
                varSelect.innerHTML += `<option value="${v.id}">${v.name}</option>`;
            });
        }

        function smartSuggestIngredients() {
            const prodSelect = document.getElementById('productSelect');
            const opt = prodSelect.options[prodSelect.selectedIndex];
            if (!opt || !opt.value) {
                alert("Please select a target product first.");
                return;
            }

            const title = (opt.text || '').toLowerCase();
            const suggestions = [];

            function findIng(keyword) {
                return ingredientsData.find(i => i.name.toLowerCase().includes(keyword.toLowerCase()));
            }

            const beans = findIng('bean') || findIng('coffee') || findIng('arabica');
            const milk = findIng('dairy') || findIng('whole') || findIng('milk');
            const oatMilk = findIng('oat');
            const vanilla = findIng('vanilla');
            const caramel = findIng('caramel');
            const matcha = findIng('matcha') || findIng('tea');
            const croissant = findIng('croissant') || findIng('dough') || findIng('bake');

            if (title.includes('croissant') || title.includes('pastry') || title.includes('bakery')) {
                if (croissant) suggestions.push({ ingredient: croissant, qty: (croissant.unit.toLowerCase() === 'box' ? 0.05 : 1), unit: croissant.unit });
            } else if (title.includes('matcha')) {
                if (matcha) suggestions.push({ ingredient: matcha, qty: (matcha.unit.toLowerCase() === 'tin' ? 0.03 : 6), unit: matcha.unit });
                if (milk) suggestions.push({ ingredient: milk, qty: (milk.unit.toLowerCase() === 'gallon' ? 0.06 : 200), unit: milk.unit });
            } else {
                // Coffee based drink
                if (beans) suggestions.push({ ingredient: beans, qty: (beans.unit.toLowerCase() === 'bag' ? 0.02 : 18), unit: beans.unit });

                if (title.includes('oat') && oatMilk) {
                    suggestions.push({ ingredient: oatMilk, qty: (oatMilk.unit.toLowerCase() === 'carton' ? 0.2 : 200), unit: oatMilk.unit });
                } else if (!title.includes('americano') && !title.includes('espresso') && milk) {
                    suggestions.push({ ingredient: milk, qty: (milk.unit.toLowerCase() === 'gallon' ? 0.05 : 180), unit: milk.unit });
                }

                if (title.includes('vanilla') && vanilla) {
                    suggestions.push({ ingredient: vanilla, qty: (vanilla.unit.toLowerCase() === 'bottle' ? 0.03 : 20), unit: vanilla.unit });
                }
                if (title.includes('caramel') && caramel) {
                    suggestions.push({ ingredient: caramel, qty: (caramel.unit.toLowerCase() === 'bottle' ? 0.03 : 20), unit: caramel.unit });
                }
            }

            if (suggestions.length === 0) {
                alert("No specific preset found for this product. You can add ingredients manually using '+ Add Row'.");
                return;
            }

            const container = document.getElementById('recipeItemsContainer');
            container.innerHTML = '';
            itemIndex = 0;

            suggestions.forEach(s => {
                let options = '<option value="">Select ingredient...</option>';
                ingredientsData.forEach(ing => {
                    options += `<option value="${ing.id}" ${ing.id === s.ingredient.id ? 'selected' : ''}>${ing.name} (${ing.unit})</option>`;
                });

                const row = document.createElement('div');
                row.className = 'flex items-center space-x-2';
                row.innerHTML = `
                    <select name="items[${itemIndex}][ingredient_id]" required class="flex-2 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                        ${options}
                    </select>
                    <input type="number" step="0.001" name="items[${itemIndex}][quantity]" placeholder="Quantity" value="${s.qty}" required class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg font-bold">
                    <input type="text" name="items[${itemIndex}][unit]" placeholder="Unit" value="${s.unit}" required class="w-28 text-xs px-3 py-1.5 border border-slate-300 rounded-lg uppercase font-semibold">
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 text-xs px-2 font-bold" title="Remove">&times;</button>
                `;
                container.appendChild(row);
                itemIndex++;
            });
        }

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
                <input type="text" name="items[${itemIndex}][unit]" placeholder="Unit (e.g. g, ml)" value="ml" required class="w-24 text-xs px-3 py-1.5 border border-slate-300 rounded-lg uppercase font-semibold">
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 text-xs px-2 font-bold">&times;</button>
            `;
            container.appendChild(row);
            itemIndex++;
        }

        document.addEventListener('DOMContentLoaded', updateVariantOptions);
    </script>
</x-app-layout>
