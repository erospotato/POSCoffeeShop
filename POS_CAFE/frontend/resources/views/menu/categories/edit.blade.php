<x-app-layout>
    <x-slot name="header">
        Edit Category: {{ $category->name }}
    </x-slot>

    <div class="max-w-xl bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('categories.update', $category->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category Name *</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">{{ old('description', $category->description) }}</textarea>
            </div>

            <div class="flex items-center space-x-2">
                <input type="checkbox" name="is_active" value="1" id="is_active" {{ $category->is_active ? 'checked' : '' }} class="rounded text-amber-500 focus:ring-amber-400">
                <label for="is_active" class="text-xs font-semibold text-slate-700">Active category visible in POS</label>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                <a href="{{ route('categories.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm">Update Category</button>
            </div>
        </form>
    </div>
</x-app-layout>
