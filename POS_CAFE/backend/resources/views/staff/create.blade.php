<x-app-layout>
    <x-slot name="header">
        Create Staff Account
    </x-slot>

    <div class="max-w-xl mx-auto">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">New Staff User</h2>
                    <p class="text-xs text-slate-500">Provide user credentials and select their operational role.</p>
                </div>
                <a href="{{ route('staff.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                    &larr; Back to Staff
                </a>
            </div>

            <form method="POST" action="{{ route('staff.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Alex Morgan"
                           class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address (Login) *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="e.g. alex@cafe.com"
                           class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="e.g. +1 555-0144"
                           class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    @error('phone') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Initial Password *</label>
                    <input type="password" name="password" required placeholder="Minimum 6 characters"
                           class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    @error('password') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Assigned Role *</label>
                    <select name="role_id" required class="w-full text-xs px-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                        <option value="">-- Select Role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }} ({{ $role->description ?? 'Standard access' }})
                            </option>
                        @endforeach
                    </select>
                    @error('role_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="pt-2">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <span class="text-xs font-semibold text-slate-700">Account Active (Permitted to log in)</span>
                    </label>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('staff.index') }}" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
