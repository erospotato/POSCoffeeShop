<x-app-layout>
    <x-slot name="header">
        Customer Directory & Loyalty
    </x-slot>

    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <form method="GET" action="{{ route('customers.index') }}" class="flex items-center space-x-3 flex-1 max-w-md">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer by name, phone, or email..." class="flex-1 text-xs px-3 py-2 border border-slate-300 rounded-xl bg-white shadow-xs">
            <button type="submit" class="px-3.5 py-2 bg-slate-900 text-white rounded-xl text-xs font-semibold">Search</button>
        </form>

        <div class="flex items-center space-x-2">
            <a href="{{ route('loyalty.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition">
                Rewards Catalog &rarr;
            </a>
            <a href="{{ route('customers.create') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm">
                + Register Customer
            </a>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Customer Name</th>
                        <th class="px-6 py-3">Phone</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Points Balance</th>
                        <th class="px-6 py-3">Lifetime Spent</th>
                        <th class="px-6 py-3">Orders</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-3.5 font-bold text-slate-900">
                                <a href="{{ route('customers.show', $customer->id) }}" class="hover:text-amber-600 hover:underline">
                                    {{ $customer->name }}
                                </a>
                            </td>
                            <td class="px-6 py-3.5 font-mono text-slate-700">{{ $customer->phone }}</td>
                            <td class="px-6 py-3.5 text-slate-500">{{ $customer->email ?? '—' }}</td>
                            <td class="px-6 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-amber-100 text-amber-800">
                                    {{ $customer->points_balance }} pts
                                </span>
                            </td>
                            <td class="px-6 py-3.5 font-extrabold text-slate-900">${{ number_format($customer->total_spent, 2) }}</td>
                            <td class="px-6 py-3.5 text-slate-600 font-semibold">{{ $customer->orders_count }} orders</td>
                            <td class="px-6 py-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $customer->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right space-x-2">
                                <a href="{{ route('customers.show', $customer->id) }}" class="font-bold text-slate-700 hover:text-amber-600">History</a>
                                <a href="{{ route('customers.edit', $customer->id) }}" class="font-bold text-slate-500 hover:text-slate-800">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">No customers registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
