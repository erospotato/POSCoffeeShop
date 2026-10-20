<x-app-layout>
    <x-slot name="header">
        Supplier Directory & Procurement
    </x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-base font-bold text-slate-900">Registered Wholesale Suppliers</h3>
            <p class="text-xs text-slate-500">Manage coffee bean plantations, dairy farms, and packaging vendors.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('purchase-orders.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition">
                Purchase Orders &rarr;
            </a>
            <a href="{{ route('suppliers.create') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm">
                + New Supplier
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3">Supplier Name</th>
                    <th class="px-6 py-3">Contact Person</th>
                    <th class="px-6 py-3">Phone</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Total Orders</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($suppliers as $supplier)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-3.5 font-bold text-slate-900">{{ $supplier->name }}</td>
                        <td class="px-6 py-3.5 text-slate-700 font-medium">{{ $supplier->contact_name ?? '—' }}</td>
                        <td class="px-6 py-3.5 font-mono text-slate-600">{{ $supplier->phone ?? '—' }}</td>
                        <td class="px-6 py-3.5 text-slate-600">{{ $supplier->email ?? '—' }}</td>
                        <td class="px-6 py-3.5 font-semibold text-slate-800">{{ $supplier->purchase_orders_count }} POs</td>
                        <td class="px-6 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $supplier->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-right space-x-2">
                            <a href="{{ route('suppliers.edit', $supplier->id) }}" class="font-bold text-slate-700 hover:text-amber-600">Edit</a>
                            <form method="POST" action="{{ route('suppliers.destroy', $supplier->id) }}" class="inline" onsubmit="return confirm('Delete this supplier?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-bold text-rose-500 hover:text-rose-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">No suppliers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
