<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100 text-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kitchen Display System (KDS) - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex flex-col overflow-hidden bg-slate-100 font-sans select-none text-slate-800">
    <!-- KDS White Header Bar -->
    <header class="h-14 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0 shadow-xs z-10">
        <div class="flex items-center space-x-4">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-slate-500 hover:text-slate-900 transition text-xs font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Exit KDS</span>
            </a>
            <div class="h-4 w-px bg-slate-200"></div>
            <div class="flex items-center space-x-2.5">
                <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="h-8 w-auto">
                <h1 class="text-sm font-black text-slate-900 tracking-tight">Veloura Barista Kitchen Display (KDS)</h1>
                <span class="text-[11px] font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">Queue Order: First to Last</span>
            </div>
        </div>

        <div class="flex items-center space-x-4 text-xs font-mono">
            <div class="flex items-center space-x-2 text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-lg">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                <span class="text-[11px] font-sans font-bold">LIVE QUEUE</span>
            </div>
            <div id="kdsClock" class="text-slate-800 font-bold bg-slate-100 px-3 py-1 rounded-lg border border-slate-200"></div>
        </div>
    </header>

    <!-- Orders Board (White Theme) -->
    <main class="flex-1 overflow-x-auto overflow-y-hidden p-6 bg-slate-100">
        <div class="h-full flex space-x-5" id="kdsOrdersContainer">
            @forelse($orders as $index => $order)
                @php
                    $elapsed = $order->created_at->diffInMinutes(now());
                    $timerClass = $elapsed >= 15 ? 'bg-rose-100 text-rose-800 border border-rose-300 font-black animate-pulse' : ($elapsed >= 8 ? 'bg-amber-100 text-amber-900 border border-amber-300 font-bold' : 'bg-slate-100 text-slate-700 border border-slate-200 font-medium');
                @endphp
                <div class="kds-card w-84 h-full bg-white rounded-3xl border border-slate-200 shadow-md flex flex-col justify-between shrink-0 overflow-hidden" data-order-id="{{ $order->id }}">
                    <!-- Card Header -->
                    <div class="p-4 border-b border-slate-100 bg-slate-50/70">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center space-x-2">
                                <span class="w-6 h-6 rounded-full bg-slate-900 text-white flex items-center justify-center text-xs font-bold font-mono">#{{ $index + 1 }}</span>
                                <span class="font-mono text-base font-black text-slate-900 tracking-tight">{{ $order->order_number }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[11px] font-mono {{ $timerClass }}">{{ $elapsed }}m ago</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span class="capitalize font-bold text-slate-700">
                                {{ str_replace('_', ' ', $order->order_type) }} {{ $order->table_number ? '• '.$order->table_number : '' }}
                            </span>
                            <span class="font-mono text-slate-400">{{ $order->created_at->format('H:i') }}</span>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-2.5">
                        @foreach($order->items as $item)
                            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80">
                                <div class="flex items-baseline justify-between text-sm">
                                    <span class="font-extrabold text-slate-900">{{ $item->quantity }}x {{ $item->item_name }}</span>
                                    @if($item->variant_name)
                                        <span class="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded ml-2">{{ $item->variant_name }}</span>
                                    @endif
                                </div>

                                @if($item->addons->isNotEmpty())
                                    <div class="mt-1.5 flex flex-wrap gap-1">
                                        @foreach($item->addons as $addon)
                                            <span class="text-[10px] font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded">
                                                + {{ $addon->addon_name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                @if($item->preparation_notes)
                                    <div class="mt-1.5 text-[11px] text-amber-800 bg-amber-50 border border-amber-200 p-1.5 rounded-lg font-mono">
                                        ⚠️ Note: {{ $item->preparation_notes }}
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        @if($order->notes)
                            <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 italic">
                                Special Order Note: "{{ $order->notes }}"
                            </div>
                        @endif
                    </div>

                    <!-- Card Action Footer (Direct Complete - No 'Ready' step needed) -->
                    <div class="p-4 border-t border-slate-100 bg-slate-50/70">
                        @if($order->status->value === 'confirmed')
                            <div class="grid grid-cols-2 gap-2">
                                <button onclick="updateOrderStatus({{ $order->id }}, 'preparing')" class="py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold rounded-xl text-xs uppercase tracking-wider transition">
                                    Preparing
                                </button>
                                <button onclick="updateOrderStatus({{ $order->id }}, 'completed')" class="py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-xs uppercase tracking-wider shadow-sm transition">
                                    Complete ✓
                                </button>
                            </div>
                        @else
                            <button onclick="updateOrderStatus({{ $order->id }}, 'completed')" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-2xl text-xs uppercase tracking-wider shadow-sm transition flex items-center justify-center space-x-1">
                                <span>✓</span>
                                <span>Complete Order</span>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 text-center py-20">
                    <div class="w-16 h-16 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-3xl mb-4">☕</div>
                    <h3 class="text-base font-bold text-slate-700">Kitchen Display is Clear</h3>
                    <p class="text-xs text-slate-500 mt-1">Confirmed orders will appear here automatically sorted from first to last.</p>
                </div>
            @endforelse
        </div>
    </main>

    <script>
        // Live Clock
        function updateClock() {
            document.getElementById('kdsClock').innerText = new Date().toLocaleTimeString();
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Status Transition AJAX
        async function updateOrderStatus(orderId, newStatus) {
            try {
                const response = await fetch(`/kds/orders/${orderId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ status: newStatus })
                });

                const data = await response.json();
                if (data.success) {
                    pollKitchenOrders(); // Refresh board immediately
                }
            } catch (err) {
                console.error("KDS error:", err);
            }
        }

        // Live Polling every 5 seconds
        async function pollKitchenOrders() {
            try {
                const response = await fetch("{{ route('kds.poll') }}");
                const data = await response.json();

                if (!data.success) return;

                const container = document.getElementById('kdsOrdersContainer');
                if (data.orders.length === 0) {
                    container.innerHTML = `
                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 text-center py-20">
                            <div class="w-16 h-16 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-3xl mb-4">☕</div>
                            <h3 class="text-base font-bold text-slate-700">Kitchen Display is Clear</h3>
                            <p class="text-xs text-slate-500 mt-1">Confirmed orders will appear here automatically sorted from first to last.</p>
                        </div>
                    `;
                    return;
                }

                let html = '';
                data.orders.forEach((order, idx) => {
                    const elapsed = order.elapsed_minutes;
                    const timerClass = elapsed >= 15 ? 'bg-rose-100 text-rose-800 border border-rose-300 font-black animate-pulse' : (elapsed >= 8 ? 'bg-amber-100 text-amber-900 border border-amber-300 font-bold' : 'bg-slate-100 text-slate-700 border border-slate-200 font-medium');

                    let itemsHtml = '';
                    order.items.forEach(item => {
                        let addons = item.addons.map(a => `<span class="text-[10px] font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded">+ ${a}</span>`).join(' ');
                        itemsHtml += `
                            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80">
                                <div class="flex items-baseline justify-between text-sm">
                                    <span class="font-extrabold text-slate-900">${item.quantity}x ${item.name}</span>
                                    ${item.variant ? `<span class="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded ml-2">${item.variant}</span>` : ''}
                                </div>
                                ${addons ? `<div class="mt-1.5 flex flex-wrap gap-1">${addons}</div>` : ''}
                                ${item.notes ? `<div class="mt-1.5 text-[11px] text-amber-800 bg-amber-50 border border-amber-200 p-1.5 rounded-lg font-mono">⚠️ Note: ${item.notes}</div>` : ''}
                            </div>
                        `;
                    });

                    let actionBtn = '';
                    if (order.status === 'confirmed') {
                        actionBtn = `
                            <div class="grid grid-cols-2 gap-2">
                                <button onclick="updateOrderStatus(${order.id}, 'preparing')" class="py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold rounded-xl text-xs uppercase tracking-wider transition">Preparing</button>
                                <button onclick="updateOrderStatus(${order.id}, 'completed')" class="py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-xs uppercase tracking-wider shadow-sm transition">Complete ✓</button>
                            </div>
                        `;
                    } else {
                        actionBtn = `
                            <button onclick="updateOrderStatus(${order.id}, 'completed')" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-2xl text-xs uppercase tracking-wider shadow-sm transition flex items-center justify-center space-x-1">
                                <span>✓</span>
                                <span>Complete Order</span>
                            </button>
                        `;
                    }

                    html += `
                        <div class="kds-card w-84 h-full bg-white rounded-3xl border border-slate-200 shadow-md flex flex-col justify-between shrink-0 overflow-hidden" data-order-id="${order.id}">
                            <div class="p-4 border-b border-slate-100 bg-slate-50/70">
                                <div class="flex items-center justify-between mb-1.5">
                                    <div class="flex items-center space-x-2">
                                        <span class="w-6 h-6 rounded-full bg-slate-900 text-white flex items-center justify-center text-xs font-bold font-mono">#${idx + 1}</span>
                                        <span class="font-mono text-base font-black text-slate-900 tracking-tight">${order.order_number}</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[11px] font-mono ${timerClass}">${elapsed}m ago</span>
                                </div>
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <span class="capitalize font-bold text-slate-700">
                                        ${order.order_type} ${order.table_number ? '• ' + order.table_number : ''}
                                    </span>
                                    <span class="font-mono text-slate-400">${order.created_at_time}</span>
                                </div>
                            </div>

                            <div class="flex-1 overflow-y-auto p-4 space-y-2.5">
                                ${itemsHtml}
                                ${order.notes ? `<div class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 italic">Special Order Note: "${order.notes}"</div>` : ''}
                            </div>

                            <div class="p-4 border-t border-slate-100 bg-slate-50/70">
                                ${actionBtn}
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;
            } catch (err) {
                console.error("KDS Polling error:", err);
            }
        }

        // Start polling every 5000ms
        setInterval(pollKitchenOrders, 5000);
    </script>
</body>
</html>
