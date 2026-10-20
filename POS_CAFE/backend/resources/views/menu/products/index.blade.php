<x-app-layout>
    <x-slot name="header">
        Menu Products & Sizes
    </x-slot>

    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <form method="GET" action="{{ route('products.index') }}" class="flex items-center space-x-3 flex-1 max-w-md">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products by name or SKU..." class="flex-1 text-xs px-3 py-2 border border-slate-300 rounded-xl bg-white shadow-xs">
            <select name="category_id" onchange="this.form.submit()" class="text-xs px-3 py-2 border border-slate-300 rounded-xl bg-white shadow-xs">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-3 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold">Filter</button>
        </form>

        <a href="{{ route('products.create') }}" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm flex items-center space-x-1.5 transition">
            <span>+</span> <span>New Product</span>
        </a>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Product Name</th>
                        <th class="px-6 py-3">SKU</th>
                        <th class="px-6 py-3">Category</th>
                        <th class="px-6 py-3">Base Price</th>
                        <th class="px-6 py-3">Sizes / Variants</th>
                        <th class="px-6 py-3">Add-ons</th>
                        <th class="px-6 py-3">Availability</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-xl overflow-hidden bg-slate-100 shrink-0 border border-slate-200">
                                        <img src="{{ $product->image_path ?: 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=600&q=80' }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    </div>
                                    <span class="font-bold text-slate-900">{{ $product->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 font-mono text-slate-500">{{ $product->sku }}</td>
                            <td class="px-6 py-3.5 text-slate-600 font-medium">{{ $product->category->name }}</td>
                            <td class="px-6 py-3.5 font-extrabold text-amber-600">${{ number_format($product->base_price, 2) }}</td>
                            <td class="px-6 py-3.5">
                                @if($product->variants->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($product->variants as $v)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-100 text-slate-700 font-medium border border-slate-200">
                                                {{ $v->name }} (+${{ number_format($v->price_modifier, 2) }})
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400">Standard</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-slate-600">{{ $product->addons->count() }} add-ons</td>
                            <td class="px-6 py-3.5">
                                <form method="POST" action="{{ route('products.toggle', $product->id) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold cursor-pointer transition {{ $product->is_available ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-rose-100 text-rose-800 hover:bg-rose-200' }}">
                                        {{ $product->is_available ? 'Available' : 'Sold Out' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-3.5 text-right space-x-2">
                                <a href="{{ route('products.edit', $product->id) }}" class="font-bold text-slate-700 hover:text-amber-600">Edit</a>
                                <button type="button" onclick="openDeleteProductModal({{ $product->id }}, '{{ addslashes($product->name) }}')" class="font-bold text-rose-500 hover:text-rose-700">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- Custom Delete Confirmation Modal Popup -->
    <div id="deleteConfirmModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 flex flex-col items-center text-center">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl mb-3 shadow-inner">
                ⚠️
            </div>
            <h3 class="text-base font-extrabold text-slate-900 mb-1">Delete Menu Product?</h3>
            <p class="text-xs text-slate-500 mb-5">
                Are you sure you want to delete <span class="font-bold text-slate-800" id="deleteTargetProductName">this product</span>? This action will remove it from the menu catalog and active POS ordering.
            </p>

            <form id="deleteProductForm" method="POST" action="" class="w-full space-y-2">
                @csrf
                @method('DELETE')
                <div class="grid grid-cols-2 gap-2 w-full">
                    <button type="button" onclick="closeDeleteProductModal()" class="py-2.5 px-4 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                        Cancel
                    </button>
                    <button type="submit" class="py-2.5 px-4 rounded-xl text-xs font-black text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition">
                        Yes, Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openDeleteProductModal(productId, productName) {
            document.getElementById('deleteTargetProductName').innerText = `"${productName}"`;
            document.getElementById('deleteProductForm').action = `/menu/products/${productId}`;
            document.getElementById('deleteConfirmModal').classList.remove('hidden');
        }

        function closeDeleteProductModal() {
            document.getElementById('deleteConfirmModal').classList.add('hidden');
        }

        // Close on backdrop click
        document.getElementById('deleteConfirmModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeDeleteProductModal();
        });
    </script>
</x-app-layout>
