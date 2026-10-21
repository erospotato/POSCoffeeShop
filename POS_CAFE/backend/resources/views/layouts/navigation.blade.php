@php
    $user = Auth::user();
    $activeShift = $user ? $user->activeShift() : null;
@endphp

<!-- Sidebar for desktop -->
<aside class="fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 text-slate-200 flex flex-col shadow-xl transition-all duration-300">
    <!-- Brand / Café Name -->
    <div class="h-16 flex items-center px-5 bg-slate-950 border-b border-slate-800">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5">
            <div class="w-10 h-10 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center p-1 shadow-sm">
                <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="w-full h-full object-contain">
            </div>
            <div>
                <h1 class="text-sm font-extrabold text-white tracking-wide leading-tight">Veloura Café</h1>
                <span class="text-[10px] text-amber-400 font-medium uppercase tracking-wider">POS & Management</span>
            </div>
        </a>
    </div>

    <!-- Navigation Menu Items -->
    <div class="flex-1 overflow-y-auto px-4 py-4 space-y-6 text-xs">
        <!-- Operations -->
        <div>
            <div class="px-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Operations</div>
            <div class="space-y-1">
                <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('dashboard') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Dashboard
                </a>

                @if($user && ($user->isAdmin() || $user->isManager() || $user->isCashier()))
                <a href="{{ route('pos.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('pos.*') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    POS Terminal
                </a>

                <a href="{{ route('shifts.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('shifts.*') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Cashier Shifts
                </a>
                @endif

                @if($user && ($user->isAdmin() || $user->isManager() || $user->isBarista()))
                <a href="{{ route('kds.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('kds.*') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    Kitchen Display (KDS)
                </a>
                @endif
            </div>
        </div>

        <!-- Menu Management -->
        @if($user && ($user->isAdmin() || $user->isManager()))
        <div>
            <div class="px-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Menu & Catalog</div>
            <div class="space-y-1">
                <a href="{{ route('products.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('products.*') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Products & Sizes
                </a>
                <a href="{{ route('categories.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('categories.*') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    Categories
                </a>
            </div>
        </div>
        @endif

        <!-- Inventory & Stock (Admin, Manager, Barista) -->
        @if($user && ($user->isAdmin() || $user->isManager() || $user->isBarista()))
        <div>
            <div class="px-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Inventory & Stock</div>
            <div class="space-y-1">
                <a href="{{ route('ingredients.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('ingredients.*') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Ingredients Stock
                </a>
                <a href="{{ route('recipes.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('recipes.*') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Product Recipes
                </a>
                <a href="{{ route('adjustments.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('adjustments.index') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    Wastage & Adjustments
                </a>
                <a href="{{ route('adjustments.ledger') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('adjustments.ledger') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Stock Ledger
                </a>
                @if($user && ($user->isAdmin() || $user->isManager()))
                <a href="{{ route('suppliers.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('suppliers.*') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Suppliers
                </a>
                <a href="{{ route('purchase-orders.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('purchase-orders.*') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Purchase Orders
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Customers & Loyalty -->
        @if($user && ($user->isAdmin() || $user->isManager() || $user->isCashier()))
        <div>
            <div class="px-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Customers & Marketing</div>
            <div class="space-y-1">
                <a href="{{ route('customers.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('customers.*') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Customer Directory
                </a>
                <a href="{{ route('loyalty.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('loyalty.*') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    Loyalty Rewards
                </a>
                @if($user->isAdmin() || $user->isManager())
                <a href="{{ route('promotions.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('promotions.*') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    Promotions & Deals
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Finance & Reports -->
        @if($user && ($user->isAdmin() || $user->isManager()))
        <div>
            <div class="px-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Finance & Reports</div>
            <div class="space-y-1">
                <a href="{{ route('expenses.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('expenses.*') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Expenses
                </a>
                <a href="{{ route('reports.sales') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('reports.*') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Sales & Profit Reports
                </a>
            </div>
        </div>
        @endif

        <!-- Employee & Staff Management (Admin and Manager) -->
        @if($user && ($user->isAdmin() || $user->isManager()))
        <div>
            <div class="px-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Team & Staff</div>
            <div class="space-y-1">
                <a href="{{ route('staff.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('staff.*') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Employees & Roles
                </a>
            </div>
        </div>
        @endif

        <!-- System Administration (Admin only) -->
        @if($user && $user->isAdmin())
        <div>
            <div class="px-2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-2">System Admin</div>
            <div class="space-y-1">
                <a href="{{ route('settings.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('settings.*') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Settings & Tax
                </a>
                <a href="{{ route('audit-logs.index') }}" class="flex items-center px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('audit-logs.*') ? 'bg-amber-600 text-white shadow-sm' : 'hover:bg-slate-800 text-slate-300' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Audit Trail
                </a>
            </div>
        </div>
        @endif
    </div>

    <!-- Active Shift Status in Sidebar Footer -->
    <div class="p-4 bg-slate-950 border-t border-slate-800">
        @if($activeShift)
            <div class="flex items-center justify-between text-xs mb-2">
                <span class="inline-flex items-center text-emerald-400 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse mr-2"></span> Shift #{{ $activeShift->id }} Open
                </span>
                @if(!$user?->isCashier())
                    <span class="text-slate-400">${{ number_format($activeShift->expected_cash, 2) }}</span>
                @endif
            </div>
        @elseif($user && $user->isCashier())
            <div class="text-xs text-amber-400 mb-2 flex items-center">
                <span class="w-2 h-2 rounded-full bg-amber-500 mr-2"></span> No Active Shift
            </div>
        @endif

        <div class="flex items-center justify-between text-xs text-slate-400">
            <div class="truncate mr-2">
                <div class="font-medium text-slate-200 truncate">{{ $user?->name }}</div>
                <div class="text-[10px] text-amber-400 uppercase font-semibold">{{ $user?->role?->name }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-white transition" title="Log Out">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>
