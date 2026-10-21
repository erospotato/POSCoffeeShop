<x-app-layout>
    <x-slot name="header">
        Barista Kitchen Overview
    </x-slot>

    <!-- Top Status Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
        <!-- Launch KDS Button Banner -->
        <div class="md:col-span-1 bg-gradient-to-tr from-indigo-700 to-indigo-600 p-6 rounded-2xl text-white shadow-md flex flex-col justify-between">
            <div>
                <span class="text-[10px] uppercase font-bold tracking-wider text-indigo-200">Full-Screen Mode</span>
                <h3 class="text-lg font-bold mt-1">Kitchen Display (KDS)</h3>
                <p class="text-xs text-indigo-100 mt-2">Open full-screen interactive board for order prep and live polling.</p>
            </div>
            <a href="{{ route('kds.index') }}" class="mt-4 inline-flex items-center justify-center px-4 py-2.5 bg-white text-indigo-700 font-bold rounded-xl shadow-sm hover:bg-indigo-50 transition text-xs">
                Launch KDS Display &rarr;
            </a>
        </div>

        <!-- Pending Confirmation / Queue -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">In Queue</p>
                <h3 class="text-3xl font-black text-amber-600">{{ $pendingCount }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">Awaiting barista pickup</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Preparing -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Currently Preparing</p>
                <h3 class="text-3xl font-black text-indigo-600">{{ $preparingCount }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">At espresso or tea bar</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
        </div>

        <!-- Ready for Pickup -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Ready on Counter</p>
                <h3 class="text-3xl font-black text-emerald-600">{{ $readyCount }}</h3>
                <p class="text-[11px] text-slate-400 mt-1">Ready for customer</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
        </div>
    </div>

    <!-- Barista Station Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <a href="{{ route('ingredients.index') }}" class="p-4 bg-white rounded-2xl border border-slate-200 hover:border-amber-400 shadow-sm transition flex items-center space-x-3 group">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-105 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-900 group-hover:text-amber-600 transition">Ingredients Stock</h4>
                <p class="text-[10px] text-slate-400">View real-time ingredient levels</p>
            </div>
        </a>

        <a href="{{ route('recipes.index') }}" class="p-4 bg-white rounded-2xl border border-slate-200 hover:border-indigo-400 shadow-sm transition flex items-center space-x-3 group">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-105 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition">Drink Recipes</h4>
                <p class="text-[10px] text-slate-400">Preparation guides & ingredients</p>
            </div>
        </a>

        <a href="{{ route('adjustments.index') }}" class="p-4 bg-white rounded-2xl border border-slate-200 hover:border-rose-400 shadow-sm transition flex items-center space-x-3 group">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:scale-105 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-900 group-hover:text-rose-600 transition">Wastage & Spillage</h4>
                <p class="text-[10px] text-slate-400">Record kitchen waste & adjustments</p>
            </div>
        </a>
    </div>

    <!-- Active Orders Preview Grid -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h4 class="font-bold text-slate-900 text-sm">Active Orders in Kitchen</h4>
                <p class="text-xs text-slate-400">Order queue with elapsed preparation time and drink customizations</p>
            </div>
            <a href="{{ route('kds.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Open Fullscreen KDS &rarr;</a>
        </div>

        @if($queueOrders->isEmpty())
            <div class="py-16 text-center text-xs text-slate-400 bg-slate-50 rounded-xl">
                <svg class="w-10 h-10 mx-auto text-emerald-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Kitchen queue is currently clear! Ready for incoming orders.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($queueOrders as $order)
                    @php
                        $elapsed = $order->created_at->diffInMinutes(now());
                        $urgencyClass = $elapsed > 15 ? 'border-rose-400 bg-rose-50/20' : ($elapsed > 8 ? 'border-amber-400 bg-amber-50/20' : 'border-slate-200');
                    @endphp
                    <div class="p-4 rounded-xl border {{ $urgencyClass }} flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-slate-900 text-sm">{{ $order->order_number }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $order->status->badgeClass() }}">
                                    {{ $order->status->label() }}
                                </span>
                            </div>

                            <div class="text-[11px] text-slate-500 mb-3 flex items-center justify-between">
                                <span class="capitalize">{{ str_replace('_', ' ', $order->order_type) }} {{ $order->table_number ? '• ' . $order->table_number : '' }}</span>
                                <span class="font-semibold text-slate-700">{{ $elapsed }} min ago</span>
                            </div>

                            <div class="space-y-1.5 text-xs text-slate-800 border-t border-slate-100 pt-2 mb-3">
                                @foreach($order->items as $item)
                                    <div>
                                        <div class="font-semibold flex justify-between">
                                            <span>{{ $item->quantity }}x {{ $item->item_name }}</span>
                                            <span class="text-slate-500 font-normal">{{ $item->variant_name }}</span>
                                        </div>
                                        @foreach($item->addons as $addon)
                                            <div class="text-[10px] text-amber-700 ml-3">+ {{ $addon->addon_name }}</div>
                                        @endforeach
                                        @if($item->preparation_notes)
                                            <div class="text-[10px] text-slate-500 italic ml-3">Note: {{ $item->preparation_notes }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if($order->notes)
                            <div class="p-2 bg-slate-100 rounded text-[11px] text-slate-600 italic mb-3">
                                "{{ $order->notes }}"
                            </div>
                        @endif

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-[10px] text-slate-400">Order at {{ $order->created_at->format('H:i') }}</span>
                            <a href="{{ route('kds.index') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Manage on KDS &rarr;</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
