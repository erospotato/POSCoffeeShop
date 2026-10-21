<x-app-layout>
    <x-slot name="header">
        Cashier Shift #{{ $shift->id }} Audit & Details
    </x-slot>

    <!-- Top Action & Status Banner -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <h3 class="text-lg font-bold text-slate-900">Shift #{{ $shift->id }} — {{ $shift->user->name }}</h3>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 font-mono">
                    {{ $shift->cashier_station ?? 'Cashier 1' }} • {{ $shift->shift_period ?? 'Morning Shift' }}
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $shift->isOpen() ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                    {{ strtoupper($shift->status) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Opened at {{ $shift->opened_at->format('M d, Y H:i:s') }}
                @if($shift->closed_at)
                    • Closed at {{ $shift->closed_at->format('M d, Y H:i:s') }}
                @endif
            </p>
        </div>

        @if($shift->isOpen())
            <div class="flex items-center space-x-2">
                <button onclick="document.getElementById('cashMovementModal').classList.remove('hidden')" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition">
                    ± Record Cash In/Out
                </button>
                <button onclick="document.getElementById('closeShiftModal').classList.remove('hidden')" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs shadow-sm transition">
                    Close Shift & Count Drawer
                </button>
            </div>
        @endif
    </div>

    <!-- Financial Breakdown KPI Cards -->
    <div class="grid grid-cols-1 {{ auth()->user()->isCashier() ? 'md:grid-cols-3' : 'md:grid-cols-4' }} gap-4 mb-8">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-1">Opening Cash Float</span>
            <div class="text-xl font-black text-slate-900">${{ number_format($shift->opening_cash, 2) }}</div>
        </div>

        @if(!auth()->user()->isCashier())
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-1">Expected Closing Cash</span>
            <div class="text-xl font-black text-slate-900">${{ number_format($shift->expected_cash, 2) }}</div>
            <span class="text-[10px] text-slate-400">Float + Cash Sales ± Drawer Movements</span>
        </div>
        @endif

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-1">Actual Counted Cash</span>
            <div class="text-xl font-black {{ $shift->actual_cash !== null ? 'text-slate-900' : 'text-slate-400' }}">
                {{ $shift->actual_cash !== null ? '$' . number_format($shift->actual_cash, 2) : 'Awaiting Count' }}
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] uppercase font-semibold text-slate-400 block mb-1">Cash Discrepancy</span>
            @if($shift->discrepancy !== null)
                @if((float) $shift->discrepancy == 0)
                    <div class="text-xl font-black text-emerald-600">$0.00 (Balanced)</div>
                @elseif((float) $shift->discrepancy > 0)
                    <div class="text-xl font-black text-blue-600">+$ {{ number_format($shift->discrepancy, 2) }} (Over)</div>
                @else
                    <div class="text-xl font-black text-rose-600">-$ {{ number_format(abs($shift->discrepancy), 2) }} (Short)</div>
                @endif
            @else
                <div class="text-xl font-black text-slate-400">—</div>
            @endif
        </div>
    </div>

    <!-- Manager Review Section -->
    @if(Auth::user()->isAdmin() || Auth::user()->isManager())
        <div class="mb-8 p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <h4 class="font-bold text-slate-900 text-sm mb-1">Manager Review & Verification</h4>
            <p class="text-xs text-slate-500 mb-3">Review discrepancy notes and sign off on this shift record.</p>

            @if($shift->reviewed_at)
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800">
                    <span class="font-bold">Reviewed by {{ $shift->manager->name }}</span> on {{ $shift->reviewed_at->format('M d, Y H:i') }}.
                    @if($shift->review_notes)
                        <div class="mt-1 text-slate-600 italic">"{{ $shift->review_notes }}"</div>
                    @endif
                </div>
            @else
                <form method="POST" action="{{ route('shifts.review', $shift->id) }}" class="flex items-center space-x-3">
                    @csrf
                    <input type="text" name="review_notes" placeholder="Optional manager verification comments..." class="flex-1 text-xs px-3 py-2 border border-slate-300 rounded-xl">
                    <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-sm">
                        Sign Off Shift
                    </button>
                </form>
            @endif
        </div>
    @endif

    <!-- Cash Movements & Shift Orders Split -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Cash Drawer Movements -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-bold text-slate-900 text-sm">Cash Drawer Movements</h4>
                @if($shift->isOpen())
                    <button onclick="document.getElementById('cashMovementModal').classList.remove('hidden')" class="text-xs font-semibold text-amber-600 hover:underline">
                        + Add Movement
                    </button>
                @endif
            </div>

            @if($shift->cashMovements->isEmpty())
                <div class="py-8 text-center text-xs text-slate-400 bg-slate-50 rounded-xl">
                    No cash in / cash out movements logged for this shift.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="text-slate-400 border-b border-slate-100 uppercase text-[10px]">
                            <tr>
                                <th class="pb-2">Time</th>
                                <th class="pb-2">Type</th>
                                <th class="pb-2">Amount</th>
                                <th class="pb-2">Reason</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($shift->cashMovements as $mv)
                                <tr>
                                    <td class="py-2.5 text-slate-500">{{ $mv->created_at->format('H:i') }}</td>
                                    <td class="py-2.5 font-bold {{ $mv->type === 'cash_in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $mv->type === 'cash_in' ? '+ Cash In' : '- Cash Out' }}
                                    </td>
                                    <td class="py-2.5 font-extrabold text-slate-900">${{ number_format($mv->amount, 2) }}</td>
                                    <td class="py-2.5 text-slate-600">{{ $mv->reason }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Shift Orders -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-bold text-slate-900 text-sm">Orders Processed in Shift ({{ $shift->orders->count() }})</h4>
                <span class="text-xs font-bold text-slate-500">
                    Total: ${{ number_format($shift->orders->sum('total_amount'), 2) }}
                </span>
            </div>

            @if($shift->orders->isEmpty())
                <div class="py-8 text-center text-xs text-slate-400 bg-slate-50 rounded-xl">
                    No orders processed during this shift yet.
                </div>
            @else
                <div class="overflow-x-auto max-h-80 overflow-y-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="text-slate-400 border-b border-slate-100 uppercase text-[10px]">
                            <tr>
                                <th class="pb-2">Order #</th>
                                <th class="pb-2">Type</th>
                                <th class="pb-2">Payment</th>
                                <th class="pb-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($shift->orders as $ord)
                                <tr>
                                    <td class="py-2 font-bold text-slate-900">
                                        <a href="{{ route('pos.receipt', $ord->id) }}" target="_blank" class="hover:text-amber-600 hover:underline">
                                            {{ $ord->order_number }}
                                        </a>
                                    </td>
                                    <td class="py-2 capitalize text-slate-500">{{ str_replace('_', ' ', $ord->order_type) }}</td>
                                    <td class="py-2 capitalize text-slate-700">
                                        {{ $ord->payments->map(fn($p) => ucfirst($p->payment_method))->join(', ') ?: 'Unpaid' }}
                                    </td>
                                    <td class="py-2 text-right font-extrabold text-slate-900">${{ number_format($ord->total_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Cash Movement Modal -->
    <div id="cashMovementModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-slate-900 mb-1">Record Cash Movement</h3>
            <p class="text-xs text-slate-500 mb-4">Add cash float injection (Cash In) or petty expense payout (Cash Out).</p>

            <form method="POST" action="{{ route('shifts.cash_movement', $shift->id) }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Movement Type</label>
                    <select name="type" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                        <option value="cash_in">Cash In (Drawer Injection / Top-up)</option>
                        <option value="cash_out">Cash Out (Petty Cash / Payout)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Amount ($)</label>
                    <input type="number" step="0.01" name="amount" required class="w-full text-sm font-bold px-3 py-2 border border-slate-300 rounded-xl">
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Reason / Note</label>
                    <input type="text" name="reason" placeholder="e.g. Additional coin rolls, Milk emergency buy" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>

                <div class="flex items-center justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('cashMovementModal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-sm">Save Movement</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Close Shift Modal -->
    <div id="closeShiftModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-slate-900 mb-1">Close Cashier Shift</h3>
            <p class="text-xs text-slate-500 mb-4">Perform final physical cash count in drawer and enter total amount.</p>

            <form method="POST" action="{{ route('shifts.close', $shift->id) }}">
                @csrf
                @if(!auth()->user()->isCashier())
                <div class="p-3 rounded-xl bg-slate-100 mb-4 text-xs">
                    <span class="text-slate-500 block">Expected Closing Cash:</span>
                    <span class="text-lg font-black text-slate-900">${{ number_format($shift->expected_cash, 2) }}</span>
                </div>
                @endif

                <div class="mb-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Actual Counted Cash ($)</label>
                    <input type="number" step="0.01" name="actual_cash" placeholder="0.00" required class="w-full text-xl font-black text-slate-900 px-3 py-2 border border-slate-300 rounded-xl">
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Closing Notes (Optional)</label>
                    <textarea name="closing_notes" rows="2" placeholder="Explain any discrepancy or handover notes..." class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('closeShiftModal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm">Finalize & Close</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
