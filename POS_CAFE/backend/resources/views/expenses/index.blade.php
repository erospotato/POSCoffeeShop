<x-app-layout>
    <x-slot name="header">
        Operating Expenses
    </x-slot>

    <!-- Top Action Bar & Filter -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <form method="GET" action="{{ route('expenses.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
            <select name="category_id" class="text-xs px-3 py-2 border border-slate-300 rounded-xl bg-white shadow-xs">
                <option value="">All Expense Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>

            <div class="flex items-center space-x-1">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="text-xs px-2.5 py-2 border border-slate-300 rounded-xl bg-white shadow-xs">
                <span class="text-xs text-slate-400">to</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="text-xs px-2.5 py-2 border border-slate-300 rounded-xl bg-white shadow-xs">
            </div>

            <button type="submit" class="px-3.5 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold">Filter</button>
            @if(request()->hasAny(['category_id', 'start_date', 'end_date']))
                <a href="{{ route('expenses.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>

        <div class="flex items-center space-x-3">
            <div class="text-right mr-2">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Filtered</span>
                <span class="text-sm font-black text-rose-600">${{ number_format($totalExpenseAmount, 2) }}</span>
            </div>
            <a href="{{ route('expenses.create') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm transition">
                + Record Expense
            </a>
        </div>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Category</th>
                        <th class="px-6 py-3">Description</th>
                        <th class="px-6 py-3">Payment Method</th>
                        <th class="px-6 py-3">Recorded By</th>
                        <th class="px-6 py-3">Amount</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-3.5 font-mono text-[11px] text-slate-600">
                                {{ \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ $expense->category?->name }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 font-medium text-slate-900 max-w-xs">{{ $expense->description }}</td>
                            <td class="px-6 py-3.5 uppercase font-mono text-[11px] text-slate-600">
                                {{ str_replace('_', ' ', $expense->payment_method) }}
                            </td>
                            <td class="px-6 py-3.5 text-slate-600">{{ $expense->user?->name ?? 'System' }}</td>
                            <td class="px-6 py-3.5 font-black text-rose-600">${{ number_format($expense->amount, 2) }}</td>
                            <td class="px-6 py-3.5 text-right">
                                <form method="POST" action="{{ route('expenses.destroy', $expense->id) }}" class="inline" onsubmit="return confirm('Delete this expense?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-rose-500 hover:text-rose-700">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">No expense records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
