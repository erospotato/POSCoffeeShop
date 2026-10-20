<x-app-layout>
    <x-slot name="header">
        Record Operating Expense
    </x-slot>

    <div class="max-w-xl mx-auto">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Expense Entry</h2>
                    <p class="text-xs text-slate-500">Record utilities, rent, maintenance, or petty cash expenses.</p>
                </div>
                <a href="{{ route('expenses.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                    &larr; Back to Expenses
                </a>
            </div>

            <form method="POST" action="{{ route('expenses.store') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Expense Category *</label>
                        <select name="expense_category_id" required class="w-full text-xs px-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('expense_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('expense_category_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Expense Date *</label>
                        <input type="date" name="expense_date" value="{{ old('expense_date', now()->toDateString()) }}" required
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                        @error('expense_date') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Amount ($) *</label>
                        <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required placeholder="0.00"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 font-bold">
                        @error('amount') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Payment Method *</label>
                        <select name="payment_method" required class="w-full text-xs px-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash (Petty Cash)</option>
                            <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Corporate Card</option>
                        </select>
                        @error('payment_method') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Description / Reason *</label>
                    <textarea name="description" rows="3" required placeholder="Describe the business expense, invoice reference, vendor..."
                              class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">{{ old('description') }}</textarea>
                    @error('description') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('expenses.index') }}" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        Save Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
