<x-app-layout>
    <x-slot name="header">
        Edit Staff: {{ $staff->name }}
    </x-slot>

    <div class="max-w-xl mx-auto">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Edit Staff Account</h2>
                    <p class="text-xs text-slate-500">Update credentials, role assignment, and access privileges.</p>
                </div>
                <a href="{{ route('staff.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                    &larr; Back to Staff
                </a>
            </div>

            <form method="POST" action="{{ route('staff.update', $staff->id) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $staff->name) }}" required
                           class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email', $staff->email) }}" required
                           class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $staff->phone) }}"
                           class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    @error('phone') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Change Password (leave blank to keep current)</label>
                    <input type="password" name="password" placeholder="New password (optional)"
                           class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    @error('password') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">System Role *</label>
                    @if(Auth::id() === $staff->id)
                        <input type="hidden" name="role_id" value="{{ $staff->role_id }}">
                        <p class="text-xs font-bold text-slate-700 bg-slate-100 px-3.5 py-2.5 rounded-xl border border-slate-200">
                            {{ $staff->role?->name }} (You cannot change your own role)
                        </p>
                    @else
                        <select name="role_id" required class="w-full text-xs px-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id', $staff->role_id) == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }} ({{ $role->description ?? 'Standard access' }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                    @error('role_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="pt-2">
                    @if(Auth::id() === $staff->id)
                        <input type="hidden" name="is_active" value="1">
                        <p class="text-xs text-slate-500 italic">Self-deactivation is disabled for security.</p>
                    @else
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $staff->is_active) ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                            <span class="text-xs font-semibold text-slate-700">Account Active (Permitted to log in)</span>
                        </label>
                    @endif
                    @error('is_active') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    @if(Auth::id() !== $staff->id)
                        <button type="button" onclick="if(confirm('Deactivate account for {{ $staff->name }}?')) document.getElementById('deactivate-staff-form').submit();"
                                class="px-4 py-2 text-xs font-bold text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition">
                            Deactivate Account
                        </button>
                    @else
                        <div></div>
                    @endif

                    <div class="flex items-center space-x-3">
                        <a href="{{ route('staff.index') }}" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50">
                            Cancel
                        </a>
                        <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-sm transition">
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>

            @if(Auth::id() !== $staff->id)
                <form id="deactivate-staff-form" method="POST" action="{{ route('staff.destroy', $staff->id) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
