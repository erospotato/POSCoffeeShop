<x-app-layout>
    <x-slot name="header">
        Staff & Role Management
    </x-slot>

    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-sm font-bold text-slate-800">Employee Accounts & Access Control</h2>
            <p class="text-xs text-slate-500">Assign role-based access: Administrator, Manager, Cashier, or Barista.</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('staff.create') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm transition">
                + Add Staff Member
            </a>
        </div>
    </div>

    @if($errors->has('staff_error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-semibold">
            {{ $errors->first('staff_error') }}
        </div>
    @endif

    <!-- Staff Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Staff Member</th>
                        <th class="px-6 py-3">Email Address</th>
                        <th class="px-6 py-3">Phone</th>
                        <th class="px-6 py-3">System Role</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Joined Date</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($staff as $member)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-3.5 font-bold text-slate-900 flex items-center space-x-2.5">
                                <div class="w-7 h-7 rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-700 text-[11px]">
                                    {{ substr($member->name, 0, 1) }}
                                </div>
                                <span>{{ $member->name }}</span>
                                @if(Auth::id() === $member->id)
                                    <span class="text-[10px] text-amber-600 font-bold">(You)</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-slate-600 font-mono">{{ $member->email }}</td>
                            <td class="px-6 py-3.5 text-slate-500">{{ $member->phone ?? '—' }}</td>
                            <td class="px-6 py-3.5">
                                @php
                                    $roleBadge = match($member->role?->name) {
                                        'Administrator' => 'bg-rose-100 text-rose-800 border-rose-200',
                                        'Manager' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                        'Cashier' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'Barista' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        default => 'bg-slate-100 text-slate-800 border-slate-200'
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black border {{ $roleBadge }}">
                                    {{ $member->role?->name ?? 'None' }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $member->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $member->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-slate-400 text-[11px]">{{ $member->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-3.5 text-right space-x-2">
                                <a href="{{ route('staff.edit', $member->id) }}" class="font-bold text-slate-600 hover:text-amber-600">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">No staff members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($staff->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $staff->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
