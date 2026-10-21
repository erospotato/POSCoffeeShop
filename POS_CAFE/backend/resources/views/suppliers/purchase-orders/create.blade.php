<x-app-layout>
    <x-slot name="header">
        Create Purchase Order
    </x-slot>

    <div class="max-w-3xl bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('purchase-orders.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Supplier *</label>
                    <select name="supplier_id" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                        <option value="">Choose Supplier...</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Order Date *</label>
                    <input type="date" name="ordered_at" value="{{ date('Y-m-d') }}" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Expected Delivery</label>
                    <input type="date" name="expected_delivery_date" value="{{ date('Y-m-d', strtotime('+3 days')) }}" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Notes / Shipping Instructions</label>
                <textarea name="notes" rows="2" placeholder="Delivery instructions or quote references..." class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">{{ old('notes') }}</textarea>
            </div>

            <!-- Ordered Line Items -->
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h4 class="font-bold text-slate-900 text-xs">Ordered Ingredients</h4>
                        <p class="text-[11px] text-slate-500">Select product ingredient and amount to order</p>
                    </div>
                    <button type="button" onclick="addPoItemRow()" class="text-xs font-bold text-amber-600 hover:text-amber-700">+ Add Ingredient</button>
                </div>

                <div class="flex items-center space-x-2 mb-1.5 px-1 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                    <span class="flex-1">Name of the Product</span>
                    <span class="w-36">Amount</span>
                    <span class="w-6"></span>
                </div>

                <div id="poItemsContainer" class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <select name="items[0][ingredient_id]" required class="flex-1 text-xs px-3 py-2 border border-slate-300 rounded-lg bg-white">
                            <option value="">Select product...</option>
                            @foreach($ingredients as $ing)
                                <option value="{{ $ing->id }}">{{ $ing->name }} ({{ $ing->unit }})</option>
                            @endforeach
                        </select>
                        <input type="number" step="0.001" name="items[0][quantity_ordered]" placeholder="Amount" value="10" required class="w-36 text-xs px-3 py-2 border border-slate-300 rounded-lg font-bold">
                        <div class="w-6"></div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a href="{{ route('purchase-orders.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm">Submit Purchase Order</button>
            </div>
        </form>
    </div>

    <script>
        const ingredientsList = @json($ingredients);
        let poItemIndex = 1;

        function addPoItemRow() {
            const container = document.getElementById('poItemsContainer');
            const row = document.createElement('div');
            row.className = 'flex items-center space-x-2';

            let options = '<option value="">Select product...</option>';
            ingredientsList.forEach(ing => {
                options += `<option value="${ing.id}">${ing.name} (${ing.unit})</option>`;
            });

            row.innerHTML = `
                <select name="items[${poItemIndex}][ingredient_id]" required class="flex-1 text-xs px-3 py-2 border border-slate-300 rounded-lg bg-white">
                    ${options}
                </select>
                <input type="number" step="0.001" name="items[${poItemIndex}][quantity_ordered]" placeholder="Amount" value="5" required class="w-36 text-xs px-3 py-2 border border-slate-300 rounded-lg font-bold">
                <button type="button" onclick="this.parentElement.remove()" class="w-6 text-rose-500 hover:text-rose-700 text-base font-bold flex items-center justify-center">&times;</button>
            `;
            container.appendChild(row);
            poItemIndex++;
        }
    </script>
</x-app-layout>
