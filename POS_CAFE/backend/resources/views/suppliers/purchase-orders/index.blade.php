<x-app-layout>
    <x-slot name="header">
        Purchase Orders & Stock Procurement
    </x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-base font-bold text-slate-900">Procurement & Supplier Invoices</h3>
            <p class="text-xs text-slate-500">Track placed orders, incoming shipments, and receive stock directly into inventory.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('suppliers.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition">
                &larr; Suppliers Directory
            </a>
            <a href="{{ route('purchase-orders.create') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm">
                + New Purchase Order
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">PO Number</th>
                        <th class="px-6 py-3">Supplier</th>
                        <th class="px-6 py-3">Created By</th>
                        <th class="px-6 py-3">Ordered Date</th>
                        <th class="px-6 py-3">Expected Delivery</th>
                        <th class="px-6 py-3">Total Cost</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchaseOrders as $po)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-3.5 font-bold font-mono text-slate-900">{{ $po->po_number }}</td>
                            <td class="px-6 py-3.5 font-semibold text-slate-800">{{ $po->supplier->name }}</td>
                            <td class="px-6 py-3.5 text-slate-600">{{ $po->creator->name }}</td>
                            <td class="px-6 py-3.5 text-slate-500">{{ $po->ordered_at ? $po->ordered_at->format('M d, Y') : '—' }}</td>
                            <td class="px-6 py-3.5 text-slate-500">{{ $po->expected_delivery_date ? $po->expected_delivery_date->format('M d, Y') : '—' }}</td>
                            <td class="px-6 py-3.5 font-extrabold text-slate-900">${{ number_format($po->total_cost, 2) }}</td>
                            <td class="px-6 py-3.5">
                                @php
                                    $badgeClass = match($po->status) {
                                        'received' => 'bg-emerald-100 text-emerald-800',
                                        'ordered' => 'bg-blue-100 text-blue-800',
                                        'cancelled' => 'bg-rose-100 text-rose-800',
                                        default => 'bg-slate-100 text-slate-600'
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $badgeClass }}">
                                    {{ strtoupper($po->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right font-bold text-amber-600 hover:underline">
                                <a href="{{ route('purchase-orders.show', $po->id) }}">View / Receive &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">No purchase orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchaseOrders->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $purchaseOrders->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
