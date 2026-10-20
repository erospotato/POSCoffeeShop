<x-app-layout>
    <x-slot name="header">
        Product Recipes (Bill of Materials)
    </x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-base font-bold text-slate-900">Configured Recipe Ingredient Deductions</h3>
            <p class="text-xs text-slate-500">Every paid POS order atomically deducts raw ingredients according to these bill of materials.</p>
        </div>
        <a href="{{ route('recipes.create') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm">
            + Create Recipe
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($recipes as $recipe)
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm">{{ $recipe->product->name }}</h4>
                            @if($recipe->variant)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                    Variant: {{ $recipe->variant->name }}
                                </span>
                            @else
                                <span class="text-[10px] text-slate-400">Default Recipe</span>
                            @endif
                        </div>
                        <div class="flex items-center space-x-1">
                            <a href="{{ route('recipes.edit', $recipe->id) }}" class="p-1 text-slate-400 hover:text-amber-600">✏️</a>
                            <form method="POST" action="{{ route('recipes.destroy', $recipe->id) }}" onsubmit="return confirm('Delete this recipe?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-slate-400 hover:text-rose-600">🗑️</button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-3 space-y-1.5 border-t border-slate-100 pt-3">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Deducted Ingredients:</span>
                        @foreach($recipe->items as $item)
                            <div class="flex items-center justify-between text-xs py-0.5">
                                <span class="text-slate-700 font-medium">• {{ $item->ingredient->name }}</span>
                                <span class="font-mono font-bold text-slate-900">{{ number_format($item->quantity, 1) }} {{ $item->unit }}</span>
                            </div>
                        @endforeach
                    </div>

                    @if($recipe->instructions)
                        <div class="mt-3 p-2.5 bg-slate-50 rounded-xl text-[11px] text-slate-500 italic">
                            "{{ $recipe->instructions }}"
                        </div>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 text-[10px] text-slate-400 flex justify-between">
                    <span>Recipe #{{ $recipe->id }}</span>
                    <span>Updated {{ $recipe->updated_at->format('M d, Y') }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-3 py-16 text-center text-slate-400 text-xs bg-white rounded-2xl border border-slate-200">
                No product recipes configured yet. Click "+ Create Recipe" above.
            </div>
        @endforelse
    </div>
</x-app-layout>
