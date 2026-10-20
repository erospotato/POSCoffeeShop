<x-app-layout>
    <x-slot name="header">
        Edit Customer: {{ $customer->name }}
    </x-slot>

    <div class="max-w-xl mx-auto">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Update Customer Profile</h2>
                    <p class="text-xs text-slate-500">Edit contact details and account status.</p>
                </div>
                <a href="{{ route('customers.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                    &larr; Back to List
                </a>
            </div>

            <form method="POST" action="{{ route('customers.update', $customer->id) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                           class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Phone Number *</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required
                           class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    @error('phone') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                           class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="pt-2">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <span class="text-xs font-semibold text-slate-700">Account Active for Loyalty & Orders</span>
                    </label>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button" onclick="if(confirm('Delete customer {{ $customer->name }}?')) document.getElementById('delete-customer-form').submit();"
                            class="px-4 py-2 text-xs font-bold text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition">
                        Delete Customer
                    </button>

                    <div class="flex items-center space-x-3">
                        <a href="{{ route('customers.index') }}" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50">
                            Cancel
                        </a>
                        <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-sm transition">
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>

            <form id="delete-customer-form" method="POST" action="{{ route('customers.destroy', $customer->id) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</x-app-layout>
