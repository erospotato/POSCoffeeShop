<x-app-layout>
    <x-slot name="header">
        Loyalty Program & Rewards
    </x-slot>

    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-sm font-bold text-slate-800">Manage Rewards & Points Ledger</h2>
            <p class="text-xs text-slate-500">Configure redeemable discounts and review customer point awards and redemptions.</p>
        </div>

        <div class="flex items-center space-x-3">
            <button onclick="document.getElementById('redeem-modal').classList.remove('hidden')"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-sm transition">
                Redeem Reward
            </button>
            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
            <button onclick="document.getElementById('reward-modal').classList.remove('hidden')"
                    class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm transition">
                + New Reward
            </button>
            @endif
        </div>
    </div>

    @if($errors->has('loyalty_error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs">
            {{ $errors->first('loyalty_error') }}
        </div>
    @endif

    <!-- Rewards Catalog Cards -->
    <div class="mb-8">
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Active Rewards Catalog</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse($rewards as $reward)
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between hover:border-amber-400 transition">
                    <div>
                        <div class="flex items-start justify-between">
                            <span class="px-2.5 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800">
                                {{ $reward->points_required }} PTS
                            </span>
                            <span class="text-[10px] font-bold uppercase {{ $reward->is_active ? 'text-emerald-600' : 'text-slate-400' }}">
                                {{ $reward->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <h4 class="text-sm font-extrabold text-slate-900 mt-3">{{ $reward->name }}</h4>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $reward->description ?? 'No description provided.' }}</p>
                    </div>

                    <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-700">
                            @if($reward->discount_type === 'fixed_amount')
                                ${{ number_format($reward->discount_value, 2) }} Off
                            @elseif($reward->discount_type === 'percentage')
                                {{ number_format($reward->discount_value, 0) }}% Off
                            @else
                                Free {{ $reward->product?->name ?? 'Product' }}
                            @endif
                        </span>
                        <button onclick="openRedeemWithReward({{ $reward->id }})" class="text-xs font-bold text-amber-600 hover:text-amber-700">
                            Redeem &rarr;
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-4 bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-400 text-xs">
                    No rewards configured yet.@if(auth()->user()->isAdmin() || auth()->user()->isManager()) Click "+ New Reward" to create one.@endif
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Recent Loyalty Activity</h3>
            <span class="text-xs text-slate-500">Live ledger</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Points</th>
                        <th class="px-6 py-3">Balance After</th>
                        <th class="px-6 py-3">Order / Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentTransactions as $txn)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-3.5 text-slate-500 font-mono text-[11px]">{{ $txn->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-3.5 font-bold text-slate-900">
                                <a href="{{ route('customers.show', $txn->customer_id) }}" class="hover:text-amber-600 hover:underline">
                                    {{ $txn->customer?->name }}
                                </a>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $txn->type === 'earned' ? 'bg-emerald-100 text-emerald-800' : ($txn->type === 'redeemed' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ ucfirst($txn->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 font-mono font-bold {{ $txn->points > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $txn->points > 0 ? '+' . $txn->points : $txn->points }} pts
                            </td>
                            <td class="px-6 py-3.5 font-bold text-slate-800">{{ $txn->balance_after }} pts</td>
                            <td class="px-6 py-3.5 text-slate-600 max-w-xs truncate">
                                @if($txn->order)
                                    <span class="font-mono text-[11px]">#{{ $txn->order->order_number }}</span> —
                                @endif
                                {{ $txn->notes ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">No loyalty transactions recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recentTransactions->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $recentTransactions->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Create Reward (Admin / Manager only) -->
    @if(auth()->user()->isAdmin() || auth()->user()->isManager())
    <div id="reward-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl p-6 border border-slate-200">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Create New Loyalty Reward</h3>
                <button onclick="document.getElementById('reward-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('loyalty.rewards.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Reward Name *</label>
                    <input type="text" name="name" required placeholder="e.g. $5 Off Any Drink"
                           class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Points Required *</label>
                    <input type="number" name="points_required" min="1" required placeholder="e.g. 50"
                           class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Discount Type *</label>
                        <select name="discount_type" id="reward-discount-type" required onchange="toggleProductField(this.value)"
                                class="w-full text-xs px-3 py-2.5 border border-slate-300 rounded-xl">
                            <option value="fixed_amount">Fixed Amount ($)</option>
                            <option value="percentage">Percentage (%)</option>
                            <option value="free_product">Free Product</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Discount Value *</label>
                        <input type="number" step="0.01" name="discount_value" required placeholder="e.g. 5.00"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <div id="product-field" class="hidden">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Eligible Free Product</label>
                    <select name="product_id" class="w-full text-xs px-3 py-2.5 border border-slate-300 rounded-xl">
                        <option value="">-- Choose Product --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (${{ number_format($p->base_price, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Optional details for customer..."
                              class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded-xl"></textarea>
                </div>

                <div class="pt-2">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-amber-600">
                        <span class="text-xs font-semibold text-slate-700">Active for Redemptions</span>
                    </label>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('reward-modal').classList.add('hidden')"
                            class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-semibold text-slate-600">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-sm">
                        Create Reward
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Modal: Redeem Reward -->
    <div id="redeem-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl p-6 border border-slate-200">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Redeem Reward for Customer</h3>
                <button onclick="document.getElementById('redeem-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('loyalty.redeem') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Select Customer *</label>
                    <select name="customer_id" required class="w-full text-xs px-3 py-2.5 border border-slate-300 rounded-xl">
                        <option value="">-- Choose Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->points_balance }} pts) - {{ $c->phone }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Select Reward *</label>
                    <select name="reward_id" id="redeem-reward-select" required class="w-full text-xs px-3 py-2.5 border border-slate-300 rounded-xl">
                        <option value="">-- Choose Reward --</option>
                        @foreach($rewards->where('is_active', true) as $r)
                            <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->points_required }} pts)</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('redeem-modal').classList.add('hidden')"
                            class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-semibold text-slate-600">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm">
                        Process Redemption
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleProductField(val) {
            const field = document.getElementById('product-field');
            if (val === 'free_product') {
                field.classList.remove('hidden');
            } else {
                field.classList.add('hidden');
            }
        }

        function openRedeemWithReward(rewardId) {
            document.getElementById('redeem-reward-select').value = rewardId;
            document.getElementById('redeem-modal').classList.remove('hidden');
        }
    </script>
</x-app-layout>
