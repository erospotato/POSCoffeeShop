<x-app-layout>
    <x-slot name="header">
        System Audit Trail & Security Logs
    </x-slot>

    <!-- Top Action Bar & Action Filter -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="flex items-center space-x-3 flex-1 max-w-md">
            <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter by action name (e.g. order, refund, shift)..."
                   class="flex-1 text-xs px-3.5 py-2 border border-slate-300 rounded-xl bg-white shadow-xs">
            <button type="submit" class="px-3.5 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold">Filter</button>
            @if(request('action'))
                <a href="{{ route('audit-logs.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Clear</a>
            @endif
        </form>

        <div class="text-xs text-slate-500 font-medium">
            Immutable audit record of all sensitive actions
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Timestamp</th>
                        <th class="px-6 py-3">User / Actor</th>
                        <th class="px-6 py-3">Action</th>
                        <th class="px-6 py-3">Target Entity</th>
                        <th class="px-6 py-3">IP Address</th>
                        <th class="px-6 py-3">Details / Changes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-3.5 text-slate-500 whitespace-nowrap">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-6 py-3.5 font-sans font-bold text-slate-900">
                                {{ $log->user?->name ?? 'System' }}
                                @if($log->user?->role)
                                    <span class="text-[10px] text-slate-400 font-normal">({{ $log->user->role->name }})</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 font-sans font-bold">
                                <span class="px-2 py-0.5 rounded-md text-[10px] uppercase font-bold
                                    @if(str_contains($log->action, 'delete') || str_contains($log->action, 'refund') || str_contains($log->action, 'cancel'))
                                        bg-rose-100 text-rose-800
                                    @elseif(str_contains($log->action, 'update') || str_contains($log->action, 'close'))
                                        bg-amber-100 text-amber-800
                                    @else
                                        bg-emerald-100 text-emerald-800
                                    @endif">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-slate-600">
                                @if($log->auditable_type)
                                    {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-slate-400">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                            <td class="px-6 py-3.5 font-mono text-[10px] text-slate-600 max-w-xs truncate" title="{{ json_encode($log->new_values) }}">
                                @if($log->new_values)
                                    {{ json_encode($log->new_values) }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-sans">No audit logs recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
