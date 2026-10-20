<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kitchen Display System (KDS) - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex flex-col overflow-hidden bg-slate-950 font-sans select-none">
    <!-- KDS Header Bar -->
    <header class="h-14 bg-slate-900 border-b border-slate-800 flex items-center justify-between px-6 shrink-0 shadow-lg">
        <div class="flex items-center space-x-4">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-slate-400 hover:text-white transition text-xs font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Exit KDS</span>
            </a>
            <div class="h-4 w-px bg-slate-800"></div>
            <div class="flex items-center space-x-2">
                <div class="w-6 h-6 rounded bg-indigo-600 flex items-center justify-center text-xs">☕</div>
                <h1 class="text-sm font-bold text-white tracking-wide">Artisan Kitchen Display System (KDS)</h1>
            </div>
        </div>

        <div class="flex items-center space-x-4 text-xs font-mono">
            <div class="flex items-center space-x-2 text-emerald-400 bg-emerald-950/60 border border-emerald-800 px-3 py-1 rounded-lg">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span class="text-[11px] font-sans font-semibold">LIVE POLLING (5s)</span>
            </div>
            <div id="kdsClock" class="text-slate-300 font-bold"></div>
        </div>
    </header>

    <!-- Orders Board -->
    <main class="flex-1 overflow-x-auto overflow-y-hidden p-6 bg-slate-950">
        <div class="h-full flex space-x-5" id="kdsOrdersContainer">
            @forelse($orders as $order)
                @php
                    $elapsed = $order->created_at->diffInMinutes(now());
                    $timerClass = $elapsed >= 15 ? 'bg-rose-500 text-white animate-pulse' : ($elapsed >= 8 ? 'bg-amber-500 text-slate-950' : 'bg-slate-800 text-slate-300');
                @endphp
                <div class="kds-card w-80 h-full bg-slate-900 rounded-2xl border border-slate-800 shadow-xl flex flex-col justify-between shrink-0" data-order-id="{{ $order->id }}">
                    <!-- Card Header -->
                    <div class="p-4 border-b border-slate-800">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="font-mono text-base font-black text-amber-400 tracking-tight">{{ $order->order_number }}</span>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold font-mono {{ $timerClass }}">{{ $elapsed }}m elapsed</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-400">
                            <span class="capitalize font-semibold text-slate-300">
                                {{ str_replace('_', ' ', $order->order_type) }} {{ $order->table_number ? '• '.$order->table_number : '' }}
                            </span>
                            <span>{{ $order->created_at->format('H:i') }}</span>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-3">
                        @foreach($order->items as $item)
                            <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/80">
                                <div class="flex items-baseline justify-between text-sm">
                                    <span class="font-extrabold text-white">{{ $item->quantity }}x {{ $item->item_name }}</span>
                                    @if($item->variant_name)
                                        <span class="text-xs text-amber-400 font-medium ml-2">{{ $item->variant_name }}</span>
                                    @endif
                                </div>

                                @if($item->addons->isNotEmpty())
                                    <div class="mt-1.5 flex flex-wrap gap-1">
                                        @foreach($item->addons as $addon)
                                            <span class="text-[10px] text-indigo-300 bg-indigo-950 border border-indigo-800/50 px-1.5 py-0.5 rounded">
                                                + {{ $addon->addon_name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                @if($item->preparation_notes)
                                    <div class="mt-1 text-[11px] text-amber-300 italic font-mono">
                                        ⚠️ Note: {{ $item->preparation_notes }}
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        @if($order->notes)
                            <div class="p-2.5 rounded-xl bg-amber-950/40 border border-amber-800/50 text-xs text-amber-200 italic">
                                Special Order Note: "{{ $order->notes }}"
                            </div>
                        @endif
                    </div>

                    <!-- Card Action Footer -->
                    <div class="p-4 border-t border-slate-800 bg-slate-900/80">
                        @if($order->status->value === 'confirmed')
                            <button onclick="updateOrderStatus({{ $order->id }}, 'preparing')" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-sm transition">
                                Start Preparing ➔
                            </button>
                        @elseif($order->status->value === 'preparing')
                            <button onclick="updateOrderStatus({{ $order->id }}, 'ready')" class="w-full py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs uppercase tracking-wider shadow-sm transition">
                                Mark as Ready ➔
                            </button>
                        @elseif($order->status->value === 'ready')
                            <button onclick="updateOrderStatus({{ $order->id }}, 'completed')" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-sm transition">
                                Complete & Dispatch ✓
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="w-full h-full flex flex-col items-center justify-center text-slate-600 text-center py-20">
                    <div class="text-6xl mb-4">✨</div>
                    <h3 class="text-lg font-bold text-slate-400">Kitchen Display is Clear</h3>
                    <p class="text-xs text-slate-600 mt-1">Confirmed and paid orders will appear here automatically.</p>
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
                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-600 text-center py-20">
                            <div class="text-6xl mb-4">✨</div>
                            <h3 class="text-lg font-bold text-slate-400">Kitchen Display is Clear</h3>
                            <p class="text-xs text-slate-600 mt-1">Confirmed and paid orders will appear here automatically.</p>
                        </div>
                    `;
                    return;
                }

                let html = '';
                data.orders.forEach(order => {
                    const elapsed = order.elapsed_minutes;
                    const timerClass = elapsed >= 15 ? 'bg-rose-500 text-white animate-pulse' : (elapsed >= 8 ? 'bg-amber-500 text-slate-950' : 'bg-slate-800 text-slate-300');

                    let itemsHtml = '';
                    order.items.forEach(item => {
                        let addons = item.addons.map(a => `<span class="text-[10px] text-indigo-300 bg-indigo-950 border border-indigo-800/50 px-1.5 py-0.5 rounded">+ ${a}</span>`).join(' ');
                        itemsHtml += `
                            <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/80">
                                <div class="flex items-baseline justify-between text-sm">
                                    <span class="font-extrabold text-white">${item.quantity}x ${item.name}</span>
                                    ${item.variant ? `<span class="text-xs text-amber-400 font-medium ml-2">${item.variant}</span>` : ''}
                                </div>
                                ${addons ? `<div class="mt-1.5 flex flex-wrap gap-1">${addons}</div>` : ''}
                                ${item.notes ? `<div class="mt-1 text-[11px] text-amber-300 italic font-mono">⚠️ Note: ${item.notes}</div>` : ''}
                            </div>
                        `;
                    });

                    let actionBtn = '';
                    if (order.status === 'confirmed') {
                        actionBtn = `<button onclick="updateOrderStatus(${order.id}, 'preparing')" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-sm transition">Start Preparing ➔</button>`;
                    } else if (order.status === 'preparing') {
                        actionBtn = `<button onclick="updateOrderStatus(${order.id}, 'ready')" class="w-full py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs uppercase tracking-wider shadow-sm transition">Mark as Ready ➔</button>`;
                    } else if (order.status === 'ready') {
                        actionBtn = `<button onclick="updateOrderStatus(${order.id}, 'completed')" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-sm transition">Complete & Dispatch ✓</button>`;
                    }

                    html += `
                        <div class="kds-card w-80 h-full bg-slate-900 rounded-2xl border border-slate-800 shadow-xl flex flex-col justify-between shrink-0" data-order-id="${order.id}">
                            <div class="p-4 border-b border-slate-800">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="font-mono text-base font-black text-amber-400 tracking-tight">${order.order_number}</span>
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold font-mono ${timerClass}">${elapsed}m elapsed</span>
                                </div>
                                <div class="flex items-center justify-between text-xs text-slate-400">
                                    <span class="capitalize font-semibold text-slate-300">
                                        ${order.order_type} ${order.table_number ? '• ' + order.table_number : ''}
                                    </span>
                                    <span>${order.created_at_time}</span>
                                </div>
                            </div>

                            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                                ${itemsHtml}
                                ${order.notes ? `<div class="p-2.5 rounded-xl bg-amber-950/40 border border-amber-800/50 text-xs text-amber-200 italic">Special Order Note: "${order.notes}"</div>` : ''}
                            </div>

                            <div class="p-4 border-t border-slate-800 bg-slate-900/80">
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
