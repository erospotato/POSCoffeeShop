<x-app-layout>
    <x-slot name="header">
        Product Categories
    </x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-base font-bold text-slate-900">Menu Category Organization</h3>
            <p class="text-xs text-slate-500">Group your products for POS quick filters and menu navigation.</p>
        </div>
        <a href="{{ route('categories.create') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm">
            + New Category
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3">Order</th>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Slug</th>
                    <th class="px-6 py-3">Products Count</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($categories as $category)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-3.5 font-bold text-slate-400">{{ $category->sort_order }}</td>
                        <td class="px-6 py-3.5 font-bold text-slate-900">{{ $category->name }}</td>
                        <td class="px-6 py-3.5 font-mono text-slate-500">{{ $category->slug }}</td>
                        <td class="px-6 py-3.5 font-semibold text-slate-700">{{ $category->products_count }} products</td>
                        <td class="px-6 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $category->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-right space-x-2">
                            <a href="{{ route('categories.edit', $category->id) }}" class="font-bold text-slate-700 hover:text-amber-600">Edit</a>
                            <form method="POST" action="{{ route('categories.destroy', $category->id) }}" class="inline" onsubmit="return confirm('Delete this category?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-bold text-rose-500 hover:text-rose-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">No categories found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
