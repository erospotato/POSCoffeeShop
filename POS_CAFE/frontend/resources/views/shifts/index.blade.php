<x-app-layout>
    <x-slot name="header">
        Cashier Shifts Management
    </x-slot>

    <!-- Top Action Row -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-base font-bold text-slate-900">Shift History & Drawer Reconciliations</h3>
            <p class="text-xs text-slate-500">Track opening float, cash movements, expected closing cash, and drawer discrepancies.</p>
        </div>

        @if(!$activeShift && (Auth::user()->isCashier() || Auth::user()->isManager() || Auth::user()->isAdmin()))
            <button onclick="document.getElementById('openShiftModal').classList.remove('hidden')" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm flex items-center space-x-1.5 transition">
                <span>💵</span> <span>Open New Shift</span>
            </button>
        @endif
    </div>

    @if($activeShift)
        <div class="mb-6 p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold">
                    ✓
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h4 class="font-bold text-slate-900 text-sm">Active Shift #{{ $activeShift->id }} ({{ $activeShift->user->name }})</h4>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">CURRENTLY OPEN</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Opened {{ $activeShift->opened_at->format('M d, Y H:i') }} | Opening Float: ${{ number_format($activeShift->opening_cash, 2) }}
                    </p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <div class="text-right mr-3">
                    <span class="text-xs text-slate-400 block">Expected Cash</span>
                    <span class="text-lg font-black text-slate-900">${{ number_format($activeShift->expected_cash, 2) }}</span>
                </div>
                <a href="{{ route('shifts.show', $activeShift->id) }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition">
                    View & Reconcile &rarr;
                </a>
            </div>
        </div>
    @endif

    <!-- Shift History Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Shift ID</th>
                        <th class="px-6 py-3">Cashier</th>
                        <th class="px-6 py-3">Opened</th>
                        <th class="px-6 py-3">Closed</th>
                        <th class="px-6 py-3">Opening Float</th>
                        <th class="px-6 py-3">Expected Cash</th>
                        <th class="px-6 py-3">Actual Count</th>
                        <th class="px-6 py-3">Discrepancy</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shifts as $shift)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-3.5 font-bold text-slate-900">#{{ $shift->id }}</td>
                            <td class="px-6 py-3.5 font-medium text-slate-800">{{ $shift->user->name }}</td>
                            <td class="px-6 py-3.5 text-slate-500">{{ $shift->opened_at->format('M d, H:i') }}</td>
                            <td class="px-6 py-3.5 text-slate-500">{{ $shift->closed_at ? $shift->closed_at->format('M d, H:i') : '—' }}</td>
                            <td class="px-6 py-3.5 font-medium text-slate-700">${{ number_format($shift->opening_cash, 2) }}</td>
                            <td class="px-6 py-3.5 font-bold text-slate-900">${{ number_format($shift->expected_cash, 2) }}</td>
                            <td class="px-6 py-3.5 font-medium text-slate-800">
                                {{ $shift->actual_cash !== null ? '$' . number_format($shift->actual_cash, 2) : '—' }}
                            </td>
                            <td class="px-6 py-3.5">
                                @if($shift->discrepancy !== null)
                                    @if((float) $shift->discrepancy == 0)
                                        <span class="text-emerald-600 font-bold">$0.00 (Balanced)</span>
                                    @elseif((float) $shift->discrepancy > 0)
                                        <span class="text-blue-600 font-bold">+$ {{ number_format($shift->discrepancy, 2) }} (Over)</span>
                                    @else
                                        <span class="text-rose-600 font-bold">-$ {{ number_format(abs($shift->discrepancy), 2) }} (Short)</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $shift->isOpen() ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ strtoupper($shift->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right font-medium">
                                <a href="{{ route('shifts.show', $shift->id) }}" class="text-amber-600 hover:text-amber-700 font-bold">
                                    Details &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-slate-400">
                                No cashier shift records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($shifts->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $shifts->links() }}
            </div>
        @endif
    </div>

    <!-- Open Shift Modal -->
    <div id="openShiftModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-slate-900 mb-1">Open Cashier Shift</h3>
            <p class="text-xs text-slate-500 mb-4">Count and declare the opening cash float in the cash drawer.</p>

            <form method="POST" action="{{ route('shifts.open') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Opening Float ($)</label>
                    <input type="number" step="0.01" name="opening_cash" value="100.00" required class="w-full text-base font-bold text-slate-900 border-slate-300 rounded-xl focus:ring-amber-500 focus:border-amber-500">
                </div>

                <div class="flex items-center justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('openShiftModal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-sm">
                        Confirm & Start Shift
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
