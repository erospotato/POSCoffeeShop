<x-app-layout>
    <x-slot name="header">
        Edit Product: {{ $product->name }}
    </x-slot>

    <div class="max-w-3xl bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('products.update', $product->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Product Name *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">SKU / Code *</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category *</label>
                    <select name="category_id" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Base Price ($) *</label>
                    <input type="number" step="0.01" name="base_price" value="{{ old('base_price', $product->base_price) }}" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-bold">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="flex items-center space-x-2">
                <input type="checkbox" name="is_available" value="1" id="is_available" {{ $product->is_available ? 'checked' : '' }} class="rounded text-amber-500 focus:ring-amber-400">
                <label for="is_available" class="text-xs font-semibold text-slate-700">Available for ordering</label>
            </div>

            <!-- Variants List -->
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h4 class="font-bold text-slate-900 text-xs">Variants & Sizes</h4>
                        <p class="text-[11px] text-slate-500">Configured variants and price deltas</p>
                    </div>
                    <button type="button" onclick="addVariantRow()" class="text-xs font-bold text-amber-600 hover:text-amber-700">+ Add Variant</button>
                </div>

                <div id="variantsContainer" class="space-y-2">
                    @forelse($product->variants as $index => $variant)
                        <div class="flex items-center space-x-2">
                            <input type="text" name="variants[{{ $index }}][name]" value="{{ $variant->name }}" placeholder="Variant Name" class="flex-2 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                            <input type="number" step="0.01" name="variants[{{ $index }}][price_modifier]" value="{{ $variant->price_modifier }}" placeholder="Price Delta" class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                            <input type="text" name="variants[{{ $index }}][sku]" value="{{ $variant->sku }}" placeholder="SKU" class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 text-xs px-2 font-bold">&times;</button>
                        </div>
                    @empty
                        <div class="flex items-center space-x-2">
                            <input type="text" name="variants[0][name]" placeholder="Variant Name" class="flex-2 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                            <input type="number" step="0.01" name="variants[0][price_modifier]" value="0.00" placeholder="Price Delta" class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                            <input type="text" name="variants[0][sku]" placeholder="SKU" class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a href="{{ route('products.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm">Update Product</button>
            </div>
        </form>
    </div>

    <script>
        let variantIndex = {{ $product->variants->count() + 1 }};
        function addVariantRow() {
            const container = document.getElementById('variantsContainer');
            const row = document.createElement('div');
            row.className = 'flex items-center space-x-2';
            row.innerHTML = `
                <input type="text" name="variants[${variantIndex}][name]" placeholder="Variant Name" class="flex-2 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                <input type="number" step="0.01" name="variants[${variantIndex}][price_modifier]" value="0.50" placeholder="Price Delta" class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                <input type="text" name="variants[${variantIndex}][sku]" placeholder="SKU" class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 text-xs px-2 font-bold">&times;</button>
            `;
            container.appendChild(row);
            variantIndex++;
        }
    </script>
</x-app-layout>
