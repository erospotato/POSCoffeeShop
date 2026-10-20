<x-app-layout>
    <x-slot name="header">
        Create New Menu Product
    </x-slot>

    <div class="max-w-3xl bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('products.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Product Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Iced Spanish Latte" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">SKU / Code *</label>
                    <input type="text" name="sku" value="{{ old('sku') }}" required placeholder="e.g. COF-SPN-001" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category *</label>
                    <select name="category_id" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Base Price ($) *</label>
                    <input type="number" step="0.01" name="base_price" value="{{ old('base_price', '3.50') }}" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-bold">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                <textarea name="description" rows="2" placeholder="Brief beverage or food tasting notes..." class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">{{ old('description') }}</textarea>
            </div>

            <div class="flex items-center space-x-2">
                <input type="checkbox" name="is_available" value="1" id="is_available" checked class="rounded text-amber-500 focus:ring-amber-400">
                <label for="is_available" class="text-xs font-semibold text-slate-700">Available for ordering immediately</label>
            </div>

            <!-- Dynamic Variants Section -->
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h4 class="font-bold text-slate-900 text-xs">Size & Temperature Variants</h4>
                        <p class="text-[11px] text-slate-500">e.g. Small, Medium, Large with price delta</p>
                    </div>
                    <button type="button" onclick="addVariantRow()" class="text-xs font-bold text-amber-600 hover:text-amber-700">+ Add Variant</button>
                </div>

                <div id="variantsContainer" class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <input type="text" name="variants[0][name]" placeholder="Variant Name (e.g. Regular)" class="flex-2 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                        <input type="number" step="0.01" name="variants[0][price_modifier]" placeholder="Price Delta ($0.00)" value="0.00" class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                        <input type="text" name="variants[0][sku]" placeholder="SKU modifier" class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a href="{{ route('products.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm">Save Product</button>
            </div>
        </form>
    </div>

    <script>
        let variantIndex = 1;
        function addVariantRow() {
            const container = document.getElementById('variantsContainer');
            const row = document.createElement('div');
            row.className = 'flex items-center space-x-2';
            row.innerHTML = `
                <input type="text" name="variants[${variantIndex}][name]" placeholder="Variant Name (e.g. Large)" class="flex-2 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                <input type="number" step="0.01" name="variants[${variantIndex}][price_modifier]" placeholder="Price Delta ($0.80)" value="0.50" class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                <input type="text" name="variants[${variantIndex}][sku]" placeholder="SKU modifier" class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 text-xs px-2 font-bold">&times;</button>
            `;
            container.appendChild(row);
            variantIndex++;
        }
    </script>
</x-app-layout>
