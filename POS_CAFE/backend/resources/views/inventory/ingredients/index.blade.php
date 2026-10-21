<x-app-layout>
    <x-slot name="header">
        Ingredients & Raw Stock Inventory
    </x-slot>

    <!-- Top Summary Banner -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs uppercase font-semibold text-slate-400 block mb-1">Total Stock Value</span>
                <div class="text-2xl font-black text-slate-900">${{ number_format($totalStockValue, 2) }}</div>
                <span class="text-[11px] text-slate-500">Documented Costing Basis</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                📦
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs uppercase font-semibold text-slate-400 block mb-1">Low Stock Alerts</span>
                <div class="text-2xl font-black {{ $lowStockCount > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                    {{ $lowStockCount }} items
                </div>
                <span class="text-[11px] text-slate-500">Below minimum threshold</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                ⚠️
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs uppercase font-semibold text-slate-400 block mb-1">Inventory Actions</span>
                <div class="flex items-center space-x-2 mt-2">
                    <a href="{{ route('ingredients.create') }}" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs transition">
                        + New Ingredient
                    </a>
                    <a href="{{ route('adjustments.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition">
                        Adjust / Waste
                    </a>
                </div>
            </div>
            <a href="{{ route('adjustments.ledger') }}" class="text-xs font-bold text-amber-600 hover:underline">
                View Ledger &rarr;
            </a>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <form method="GET" action="{{ route('ingredients.index') }}" class="flex items-center space-x-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ingredient or SKU..." class="text-xs px-3 py-2 border border-slate-300 rounded-xl">
                <label class="flex items-center space-x-1.5 text-xs text-slate-700 font-medium cursor-pointer">
                    <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} onchange="this.form.submit()" class="rounded text-amber-500 focus:ring-amber-400">
                    <span>Low Stock Only</span>
                </label>
                <button type="submit" class="px-3 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Ingredient Name</th>
                        <th class="px-6 py-3">SKU</th>
                        <th class="px-6 py-3">Unit</th>
                        <th class="px-6 py-3">Current Stock</th>
                        <th class="px-6 py-3">Low-Stock Alert Level</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ingredients as $ingredient)
                        @php
                            $isLow = (float) $ingredient->current_stock <= (float) $ingredient->min_stock_alert;
                            $stockNum = (float)$ingredient->current_stock;
                            $alertNum = (float)$ingredient->min_stock_alert;
                            $formattedStock = ($stockNum == (int)$stockNum) ? (int)$stockNum : rtrim(rtrim(number_format($stockNum, 2), '0'), '.');
                            $formattedAlert = ($alertNum == (int)$alertNum) ? (int)$alertNum : rtrim(rtrim(number_format($alertNum, 2), '0'), '.');
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition {{ $isLow ? 'bg-rose-50/30' : '' }}">
                            <td class="px-6 py-3.5 font-bold text-slate-900">
                                {{ $ingredient->name }}
                                @if($isLow)
                                    <span class="ml-1.5 px-2 py-0.5 rounded-full text-[9px] font-black bg-rose-100 text-rose-700">LOW</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 font-mono text-slate-500">{{ $ingredient->sku }}</td>
                            <td class="px-6 py-3.5 font-semibold text-slate-700">{{ $ingredient->unit }}</td>
                            <td class="px-6 py-3.5 font-extrabold {{ $isLow ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ $formattedStock }} {{ $ingredient->unit }}
                            </td>
                            <td class="px-6 py-3.5 text-slate-500 font-medium">{{ $formattedAlert }} {{ $ingredient->unit }}</td>
                            <td class="px-6 py-3.5 text-right space-x-2">
                                <a href="{{ route('ingredients.edit', $ingredient->id) }}" class="font-bold text-slate-700 hover:text-amber-600">Edit</a>
                                <form method="POST" action="{{ route('ingredients.destroy', $ingredient->id) }}" class="inline" onsubmit="return confirm('Delete this ingredient?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-bold text-rose-500 hover:text-rose-700">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">No ingredients found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($ingredients->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $ingredients->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
