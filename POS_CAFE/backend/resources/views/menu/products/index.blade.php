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
                            <td class="px-6 py-3.5 font-bold text-slate-900">{{ $product->name }}</td>
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
                                <form method="POST" action="{{ route('products.destroy', $product->id) }}" class="inline" onsubmit="return confirm('Delete this product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-bold text-rose-500 hover:text-rose-700">Delete</button>
                                </form>
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
</x-app-layout>
