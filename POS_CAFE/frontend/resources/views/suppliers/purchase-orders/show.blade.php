<x-app-layout>
    <x-slot name="header">
        Purchase Order #{{ $purchaseOrder->po_number }}
    </x-slot>

    <!-- Top Summary Banner -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <h3 class="text-lg font-bold text-slate-900">{{ $purchaseOrder->po_number }}</h3>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $purchaseOrder->isReceived() ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                    {{ strtoupper($purchaseOrder->status) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Supplier: <span class="font-bold text-slate-800">{{ $purchaseOrder->supplier->name }}</span>
                • Ordered {{ $purchaseOrder->ordered_at ? $purchaseOrder->ordered_at->format('M d, Y') : '—' }}
                • Created by {{ $purchaseOrder->creator->name }}
            </p>
        </div>

        @if(!$purchaseOrder->isReceived())
            <button onclick="document.getElementById('receiveModal').classList.remove('hidden')" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-sm flex items-center space-x-1.5 transition">
                <span>📦</span> <span>Receive Stock Delivery</span>
            </button>
        @endif
    </div>

    <!-- PO Details & Items -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h4 class="font-bold text-slate-900 text-sm">Purchase Order Items</h4>
            <span class="text-xs font-bold text-slate-900">Total: ${{ number_format($purchaseOrder->total_cost, 2) }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Ingredient</th>
                        <th class="px-6 py-3">Unit Cost</th>
                        <th class="px-6 py-3">Quantity Ordered</th>
                        <th class="px-6 py-3">Quantity Received</th>
                        <th class="px-6 py-3">Remaining to Receive</th>
                        <th class="px-6 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @foreach($purchaseOrder->items as $item)
                        @php
                            $remaining = max(0, (float)$item->quantity_ordered - (float)$item->quantity_received);
                        @endphp
                        <tr>
                            <td class="px-6 py-3.5 font-bold font-sans text-slate-900">{{ $item->ingredient->name }}</td>
                            <td class="px-6 py-3.5 text-slate-700">${{ number_format($item->unit_price, 4) }}</td>
                            <td class="px-6 py-3.5 font-bold text-slate-900">{{ number_format($item->quantity_ordered, 1) }} {{ $item->ingredient->unit }}</td>
                            <td class="px-6 py-3.5 text-emerald-600 font-bold">{{ number_format($item->quantity_received, 1) }} {{ $item->ingredient->unit }}</td>
                            <td class="px-6 py-3.5 {{ $remaining > 0 ? 'text-amber-600' : 'text-slate-400' }} font-bold">
                                {{ number_format($remaining, 1) }} {{ $item->ingredient->unit }}
                            </td>
                            <td class="px-6 py-3.5 text-right font-black text-slate-900">${{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Receipts History -->
    @if($purchaseOrder->receipts->isNotEmpty())
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h4 class="font-bold text-slate-900 text-sm mb-3">Delivery Reception History</h4>
            <div class="space-y-2 text-xs">
                @foreach($purchaseOrder->receipts as $receipt)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-800">Delivery Received by {{ $receipt->receivedByUser->name }}</span>
                            <span class="text-slate-400 text-[11px] ml-2">on {{ $receipt->received_at->format('M d, Y H:i') }}</span>
                            @if($receipt->notes)
                                <div class="text-slate-500 italic mt-0.5">"{{ $receipt->notes }}"</div>
                            @endif
                        </div>
                        <span class="text-emerald-600 font-semibold text-[11px]">✓ Inventory Stock Incremented</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Receive Stock Delivery Modal -->
    <div id="receiveModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-slate-900 mb-1">Receive Stock Delivery</h3>
            <p class="text-xs text-slate-500 mb-4">Enter actual quantities delivered. This will immediately update stock levels in MySQL.</p>

            <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder->id) }}">
                @csrf
                <div class="space-y-3 mb-4 max-h-60 overflow-y-auto pr-1">
                    @foreach($purchaseOrder->items as $item)
                        @php
                            $rem = max(0, (float)$item->quantity_ordered - (float)$item->quantity_received);
                        @endphp
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-xs text-slate-900 block">{{ $item->ingredient->name }}</span>
                                <span class="text-[10px] text-slate-400">Remaining to receive: {{ number_format($rem, 1) }} {{ $item->ingredient->unit }}</span>
                            </div>
                            <div class="w-32">
                                <input type="number" step="0.001" name="quantities[{{ $item->id }}]" value="{{ $rem }}" max="{{ $rem }}" class="w-full text-xs px-2.5 py-1.5 font-bold border border-slate-300 rounded-lg">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Delivery Notes / Invoice No.</label>
                    <input type="text" name="notes" placeholder="e.g. Carrier tracking #, all packages sealed in good condition" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>

                <div class="flex items-center justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('receiveModal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm">
                        Confirm Receipt & Update Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
