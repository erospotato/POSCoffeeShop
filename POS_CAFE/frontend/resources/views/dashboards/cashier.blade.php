<x-app-layout>
    <x-slot name="header">
        Cashier Terminal Dashboard
    </x-slot>

    <!-- Shift Warning or Active Shift Details -->
    @if(!$activeShift)
        <div class="mb-8 p-6 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-600 text-white shadow-md flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold">No Cashier Shift Currently Active</h3>
                <p class="text-xs text-amber-100 mt-1">You must open a cashier float shift with opening cash balance before processing POS sales.</p>
            </div>
            <button onclick="document.getElementById('openShiftModal').classList.remove('hidden')" class="px-5 py-2.5 bg-white text-amber-700 font-bold rounded-xl shadow hover:bg-amber-50 transition text-xs">
                Open Cashier Shift
            </button>
        </div>
    @else
        <div class="mb-8 p-6 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h3 class="text-base font-bold text-slate-900">Active Shift #{{ $activeShift->id }}</h3>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">OPEN</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Opened at {{ $activeShift->opened_at->format('M d, H:i') }} | Float: ${{ number_format($activeShift->opening_cash, 2) }}
                </p>
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ route('pos.index') }}" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-sm transition text-xs flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Go to POS Terminal
                </a>

                <a href="{{ route('shifts.show', $activeShift->id) }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition text-xs">
                    Shift Details & Close
                </a>
            </div>
        </div>
    @endif

    <!-- Recent Transactions & Shift Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Today's Processed Orders -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="font-bold text-slate-900 text-sm">Your Recent Orders Today</h4>
                    <p class="text-xs text-slate-400">Transactions processed by your account today</p>
                </div>
                <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg">
                    Total: ${{ number_format($todaySales, 2) }}
                </span>
            </div>

            @if($todayOrders->isEmpty())
                <div class="py-12 text-center text-xs text-slate-400 bg-slate-50 rounded-xl">
                    No orders processed yet today. Launch the POS terminal to start ringing up customers!
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="text-slate-400 border-b border-slate-100 uppercase text-[10px]">
                            <tr>
                                <th class="pb-2">Order #</th>
                                <th class="pb-2">Time</th>
                                <th class="pb-2">Type</th>
                                <th class="pb-2">Status</th>
                                <th class="pb-2 text-right">Amount</th>
                                <th class="pb-2 text-right">Receipt</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($todayOrders as $order)
                                <tr>
                                    <td class="py-2.5 font-bold text-slate-900">{{ $order->order_number }}</td>
                                    <td class="py-2.5 text-slate-500">{{ $order->created_at->format('H:i') }}</td>
                                    <td class="py-2.5 capitalize text-slate-600">{{ str_replace('_', ' ', $order->order_type) }}</td>
                                    <td class="py-2.5">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $order->status->badgeClass() }}">
                                            {{ $order->status->label() }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-right font-extrabold text-slate-900">${{ number_format($order->total_amount, 2) }}</td>
                                    <td class="py-2.5 text-right">
                                        <a href="{{ route('pos.receipt', $order->id) }}" target="_blank" class="text-amber-600 hover:underline font-semibold">Print</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Quick Help & Shortcuts -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <h4 class="font-bold text-slate-900 text-sm">Quick Cashier Actions</h4>

            <div class="space-y-2 text-xs">
                <a href="{{ route('pos.index') }}" class="block p-3 rounded-xl bg-amber-50 text-amber-900 hover:bg-amber-100 transition font-medium">
                    🛒 Open Point of Sale (POS)
                </a>
                <a href="{{ route('customers.index') }}" class="block p-3 rounded-xl bg-slate-50 text-slate-700 hover:bg-slate-100 transition font-medium">
                    👥 Look Up Customer Loyalty Points
                </a>
                <a href="{{ route('shifts.index') }}" class="block p-3 rounded-xl bg-slate-50 text-slate-700 hover:bg-slate-100 transition font-medium">
                    💵 View Cashier Shift Ledger
                </a>
            </div>

            <div class="pt-4 border-t border-slate-100 text-[11px] text-slate-500">
                <p class="font-semibold text-slate-700 mb-1">Cashier Guidelines:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Always confirm received cash before tendering change.</li>
                    <li>Record any petty cash withdrawals via Cash Out.</li>
                    <li>Refunds require manager or admin authorization.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Open Shift Modal -->
    <div id="openShiftModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 flex flex-col space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <h3 class="text-sm font-extrabold text-slate-900">Choose Cashier Shift</h3>
                </div>
                <button type="button" onclick="document.getElementById('openShiftModal').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-base">&times;</button>
            </div>

            <p class="text-xs text-slate-500">Select your station, work period, and verify your starting cash drawer float count.</p>

            <form method="POST" action="{{ route('shifts.open') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Cashier Station / Register *</label>
                    <select name="cashier_station" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-bold bg-white text-slate-800">
                        <option value="Cashier 1">Cashier 1 (Main Register)</option>
                        <option value="Cashier 2">Cashier 2 (Express Register)</option>
                        <option value="Cashier 3">Cashier 3 (Terrace / Drive-thru)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Shift Period *</label>
                    <select name="shift_period" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-bold bg-white text-slate-800">
                        <option value="Morning">Morning Shift (06:00 - 14:00)</option>
                        <option value="Evening">Evening Shift (14:00 - 22:00)</option>
                        <option value="Full Day">Full Day Shift</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Opening Cash Float ($) *</label>
                    <input type="number" step="0.01" name="opening_cash" value="100.00" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-mono font-bold text-slate-900">
                </div>

                <div class="pt-2 flex items-center space-x-2">
                    <button type="button" onclick="document.getElementById('openShiftModal').classList.add('hidden')" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-extrabold rounded-xl text-xs shadow-md transition">
                        Start Shift
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(!$activeShift)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('openShiftModal')?.classList.remove('hidden');
        });
    </script>
    @endif
</x-app-layout>
