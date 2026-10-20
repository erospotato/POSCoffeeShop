<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS Terminal - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-100 overflow-hidden flex flex-col">
    <!-- Top Bar -->
    <header class="h-14 bg-slate-900 text-white flex items-center justify-between px-6 shrink-0 shadow-md">
        <div class="flex items-center space-x-4">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-slate-300 hover:text-white transition text-xs font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Dashboard</span>
            </a>
            <div class="h-4 w-px bg-slate-700"></div>
            <div class="flex items-center space-x-2">
                <div class="w-6 h-6 rounded bg-amber-500 flex items-center justify-center text-xs font-bold text-white">☕</div>
                <h1 class="text-sm font-bold tracking-tight">Artisan Café POS</h1>
            </div>
        </div>

        <div class="flex items-center space-x-4 text-xs">
            @if($activeShift)
                <div class="flex items-center space-x-2 bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-700">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="font-medium text-slate-200">Shift #{{ $activeShift->id }}</span>
                    <span class="text-slate-400">• Expected: ${{ number_format($activeShift->expected_cash, 2) }}</span>
                </div>
            @else
                <div class="flex items-center space-x-2 bg-amber-950/80 text-amber-300 px-3 py-1.5 rounded-lg border border-amber-800">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <span>No Open Shift</span>
                    <a href="{{ route('shifts.index') }}" class="underline font-bold ml-1">Open Now</a>
                </div>
            @endif

            <span class="text-slate-400">{{ Auth::user()->name }} ({{ Auth::user()->role->name }})</span>
            <div id="liveClock" class="font-mono text-slate-400"></div>
        </div>
    </header>

    <!-- Main POS Interface Grid -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Left: Catalog & Categories -->
        <div class="flex-1 flex flex-col p-4 overflow-hidden border-r border-slate-200">
            <!-- Search & Filters -->
            <div class="flex items-center space-x-3 mb-4 shrink-0">
                <div class="flex-1 relative">
                    <svg class="w-4 h-4 absolute left-3 top-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="posSearchInput" placeholder="Search coffee, drinks, pastries by name or SKU..." class="w-full pl-9 pr-4 py-2 bg-white border border-slate-300 rounded-xl text-xs focus:ring-amber-500 focus:border-amber-500 shadow-sm">
                </div>
            </div>

            <!-- Category Pills -->
            <div class="flex items-center space-x-2 pb-3 shrink-0 overflow-x-auto" id="categoryFilterContainer">
                <button onclick="filterCategory('all', this)" class="category-btn active px-3.5 py-1.5 rounded-xl text-xs font-bold bg-amber-500 text-white shadow-sm transition">
                    All Menu Items
                </button>
                @foreach($categories as $category)
                    <button onclick="filterCategory('{{ $category->id }}', this)" class="category-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 transition shadow-sm whitespace-nowrap">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>

            <!-- Product Cards Grid -->
            <div class="flex-1 overflow-y-auto pr-1">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5" id="productGrid">
                    @foreach($allProducts as $product)
                        <div class="product-card bg-white p-3.5 rounded-2xl border border-slate-200 hover:border-amber-400 hover:shadow-md transition cursor-pointer flex flex-col justify-between"
                             data-category-id="{{ $product->category_id }}"
                             data-name="{{ strtolower($product->name) }}"
                             data-sku="{{ strtolower($product->sku) }}"
                             onclick="selectProduct({{ json_encode($product) }})">
                            <div>
                                <div class="w-full h-24 rounded-xl bg-gradient-to-tr from-amber-100 to-amber-50 flex items-center justify-center text-3xl mb-2.5">
                                    ☕
                                </div>
                                <h3 class="font-bold text-slate-900 text-xs leading-snug line-clamp-2">{{ $product->name }}</h3>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ $product->category->name }}</p>
                            </div>
                            <div class="mt-3 flex items-center justify-between pt-2 border-t border-slate-100">
                                <span class="font-extrabold text-amber-600 text-sm">${{ number_format($product->base_price, 2) }}</span>
                                <span class="w-6 h-6 rounded-lg bg-amber-50 text-amber-600 font-bold flex items-center justify-center text-xs">+</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right: Reactive Shopping Cart & Checkout Panel -->
        <div class="w-96 bg-white flex flex-col border-l border-slate-200 shadow-xl shrink-0">
            <!-- Order Parameters -->
            <div class="p-4 border-b border-slate-200 space-y-3 bg-slate-50/50">
                <!-- Order Type Tabs -->
                <div class="grid grid-cols-3 gap-1 bg-slate-200 p-1 rounded-xl text-xs font-semibold">
                    <button type="button" onclick="setOrderType('dine_in', this)" class="order-type-btn active py-1.5 rounded-lg bg-white text-slate-900 shadow-sm text-center">Dine-in</button>
                    <button type="button" onclick="setOrderType('takeaway', this)" class="order-type-btn py-1.5 rounded-lg text-slate-600 hover:text-slate-900 text-center">Takeaway</button>
                    <button type="button" onclick="setOrderType('delivery', this)" class="order-type-btn py-1.5 rounded-lg text-slate-600 hover:text-slate-900 text-center">Delivery</button>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-0.5">Table No.</label>
                        <input type="text" id="orderTableNumber" placeholder="e.g. Table 4" class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-0.5">Customer</label>
                        <select id="orderCustomerId" onchange="cartUpdated()" class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs">
                            <option value="">Walk-in Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" data-points="{{ $customer->points_balance }}">
                                    {{ $customer->name }} ({{ $customer->points_balance }} pts)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-0.5">Apply Promotion</label>
                    <select id="orderPromotionId" onchange="cartUpdated()" class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs">
                        <option value="">No Promotion Applied</option>
                        @foreach($promotions as $promo)
                            <option value="{{ $promo->id }}">
                                {{ $promo->name }} ({{ $promo->type === 'percentage' ? $promo->value.'%' : '$'.$promo->value }} off)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Cart Items List -->
            <div class="flex-1 overflow-y-auto p-4 space-y-2.5" id="cartItemsList">
                <div class="py-16 text-center text-slate-400 text-xs" id="emptyCartMessage">
                    <div class="text-3xl mb-2">🛒</div>
                    Your cart is currently empty.<br>Click any menu item to start order.
                </div>
            </div>

            <!-- Cart Summary & Calculation Box -->
            <div class="p-4 border-t border-slate-200 bg-slate-50 space-y-2 text-xs">
                <div class="flex justify-between text-slate-500">
                    <span>Subtotal</span>
                    <span id="cartSubtotal" class="font-semibold text-slate-800">$0.00</span>
                </div>
                <div class="flex justify-between text-emerald-600" id="discountRow">
                    <span>Promotion Discount</span>
                    <span id="cartDiscount" class="font-semibold">-$0.00</span>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>Tax ({{ $taxRate }}% {{ $taxMode }})</span>
                    <span id="cartTax" class="font-semibold text-slate-800">$0.00</span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between items-baseline">
                    <span class="text-sm font-bold text-slate-900">Total Amount</span>
                    <span id="cartTotal" class="text-2xl font-black text-amber-600">$0.00</span>
                </div>

                <div class="grid grid-cols-4 gap-2 pt-2">
                    <button onclick="clearCart()" class="col-span-1 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl transition text-xs">
                        Clear
                    </button>
                    <button onclick="openPaymentModal()" id="btnCheckout" class="col-span-3 py-3 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-extrabold rounded-xl shadow transition text-sm flex items-center justify-center space-x-1.5" disabled>
                        <span>Checkout / Pay</span>
                        <span id="btnCheckoutTotal">$0.00</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Customization Modal (Variants & Addons) -->
    <div id="customizationModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900" id="modalProductName">Product Name</h3>
                    <p class="text-xs text-slate-400" id="modalProductDescription"></p>
                </div>
                <button onclick="closeCustomizationModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <!-- Variants / Sizes -->
            <div class="mb-4" id="modalVariantsContainer">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Select Size / Variant</label>
                <div class="grid grid-cols-2 gap-2" id="modalVariantsList"></div>
            </div>

            <!-- Addons -->
            <div class="mb-4" id="modalAddonsContainer">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Add-ons & Syrups</label>
                <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1" id="modalAddonsList"></div>
            </div>

            <!-- Item Preparation Note -->
            <div class="mb-4">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Preparation Note (Optional)</label>
                <input type="text" id="modalItemNote" placeholder="e.g. Less sugar, hot, extra foam" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <div class="text-xs">
                    <span class="text-slate-400">Total Price: </span>
                    <span class="text-lg font-black text-amber-600" id="modalTotalPrice">$0.00</span>
                </div>
                <button type="button" onclick="confirmAddToCart()" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow text-xs">
                    Add to Cart
                </button>
            </div>
        </div>
    </div>

    <!-- Payment & Checkout Modal -->
    <div id="paymentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="text-base font-bold text-slate-900">Process Order Payment</h3>
                <button onclick="closePaymentModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <!-- Amount Due Banner -->
            <div class="p-4 rounded-xl bg-slate-900 text-white flex items-center justify-between mb-4">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-semibold">Total Amount Due</span>
                    <div class="text-2xl font-black text-amber-400" id="payModalAmountDue">$0.00</div>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 uppercase font-semibold">Remaining Balance</span>
                    <div class="text-lg font-bold text-emerald-400" id="payModalRemaining">$0.00</div>
                </div>
            </div>

            <!-- Payment Method Selector Tabs -->
            <div class="grid grid-cols-3 gap-2 mb-4">
                <button type="button" onclick="selectPaymentMethod('cash', this)" class="pay-method-tab active py-2 rounded-xl text-xs font-bold bg-amber-500 text-white shadow-sm flex items-center justify-center space-x-1">
                    <span>💵</span> <span>Cash</span>
                </button>
                <button type="button" onclick="selectPaymentMethod('card', this)" class="pay-method-tab py-2 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 flex items-center justify-center space-x-1">
                    <span>💳</span> <span>Card</span>
                </button>
                <button type="button" onclick="selectPaymentMethod('qr_code', this)" class="pay-method-tab py-2 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 flex items-center justify-center space-x-1">
                    <span>📱</span> <span>QR Code</span>
                </button>
            </div>

            <!-- Cash Payment Form -->
            <div id="payCashSection" class="space-y-3 mb-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Cash Received ($)</label>
                    <input type="number" step="0.01" id="cashReceivedInput" oninput="calculateCashChange()" class="w-full text-xl font-black text-slate-900 border-slate-300 rounded-xl px-3 py-2">
                </div>

                <!-- Quick Tender Buttons -->
                <div class="grid grid-cols-5 gap-1.5">
                    <button type="button" onclick="quickTender('exact')" class="py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700">Exact</button>
                    <button type="button" onclick="quickTender(5)" class="py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700">$5</button>
                    <button type="button" onclick="quickTender(10)" class="py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700">$10</button>
                    <button type="button" onclick="quickTender(20)" class="py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700">$20</button>
                    <button type="button" onclick="quickTender(50)" class="py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700">$50</button>
                </div>

                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs">
                    <span class="font-bold text-emerald-800">Change Due:</span>
                    <span class="text-lg font-black text-emerald-700" id="cashChangeDisplay">$0.00</span>
                </div>
            </div>

            <!-- Card / QR Code Section -->
            <div id="payDigitalSection" class="hidden space-y-3 mb-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center text-xs text-slate-600">
                    <div class="text-3xl mb-1" id="digitalIcon">💳</div>
                    <span class="font-semibold text-slate-800" id="digitalTitle">Simulated Card Reader</span>
                    <p class="text-[11px] text-slate-500 mt-0.5">Approved automatically in simulation mode.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Transaction Ref (Optional)</label>
                    <input type="text" id="digitalRefInput" placeholder="Auto-generated if empty" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>
            </div>

            <!-- Complete Order Action -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                <button type="button" onclick="closePaymentModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                <button type="button" id="btnSubmitOrder" onclick="submitOrderCheckout()" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-xl shadow text-sm">
                    Confirm & Complete Order
                </button>
            </div>
        </div>
    </div>

    <!-- Receipt / Order Confirmation Modal -->
    <div id="receiptModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl text-center">
            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl mx-auto mb-3">✓</div>
            <h3 class="text-base font-bold text-slate-900">Order Completed Successfully!</h3>
            <p class="text-xs text-slate-500 mt-1" id="receiptModalOrderNumber">ORD-0000</p>

            <div class="mt-6 flex flex-col space-y-2">
                <a id="receiptPrintLink" href="#" target="_blank" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition">
                    Print Thermal Customer Receipt
                </a>
                <button onclick="resetPosForNextOrder()" class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs transition">
                    New Order
                </button>
            </div>
        </div>
    </div>

    <!-- POS JavaScript Implementation -->
    <script>
        // Global POS state
        let cart = [];
        let activeOrderType = 'dine_in';
        let currentModalProduct = null;
        let selectedVariantId = null;
        let selectedAddonIds = [];
        let calculatedTotals = { subtotal: 0, discount: 0, tax: 0, total: 0 };
        let currentPaymentMethod = 'cash';

        // Live Clock
        function updateClock() {
            const el = document.getElementById('liveClock');
            if (el) el.innerText = new Date().toLocaleTimeString();
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Search Filter
        document.getElementById('posSearchInput')?.addEventListener('input', function (e) {
            const term = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.product-card').forEach(card => {
                const name = card.dataset.name;
                const sku = card.dataset.sku;
                card.style.display = (name.includes(term) || sku.includes(term)) ? 'flex' : 'none';
            });
        });

        // Category Filter
        function filterCategory(catId, btn) {
            document.querySelectorAll('.category-btn').forEach(b => {
                b.classList.remove('active', 'bg-amber-500', 'text-white');
                b.classList.add('bg-white', 'text-slate-700');
            });
            btn.classList.add('active', 'bg-amber-500', 'text-white');
            btn.classList.remove('bg-white', 'text-slate-700');

            document.querySelectorAll('.product-card').forEach(card => {
                if (catId === 'all' || card.dataset.categoryId === catId) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function setOrderType(type, btn) {
            activeOrderType = type;
            document.querySelectorAll('.order-type-btn').forEach(b => {
                b.classList.remove('active', 'bg-white', 'text-slate-900', 'shadow-sm');
                b.classList.add('text-slate-600');
            });
            btn.classList.add('active', 'bg-white', 'text-slate-900', 'shadow-sm');
            btn.classList.remove('text-slate-600');
        }

        // Product Selection
        function selectProduct(product) {
            currentModalProduct = product;
            selectedVariantId = null;
            selectedAddonIds = [];
            document.getElementById('modalItemNote').value = '';

            document.getElementById('modalProductName').innerText = product.name;
            document.getElementById('modalProductDescription').innerText = product.description || '';

            // If no variants and no addons, add immediately
            if ((!product.active_variants || product.active_variants.length === 0) &&
                (!product.active_addons || product.active_addons.length === 0)) {
                addToCartDirect(product);
                return;
            }

            // Populate Variants
            const varContainer = document.getElementById('modalVariantsContainer');
            const varList = document.getElementById('modalVariantsList');
            varList.innerHTML = '';

            if (product.active_variants && product.active_variants.length > 0) {
                varContainer.classList.remove('hidden');
                product.active_variants.forEach((v, idx) => {
                    if (idx === 0) selectedVariantId = v.id;
                    const mod = parseFloat(v.price_modifier);
                    const modText = mod > 0 ? `(+$${mod.toFixed(2)})` : '';
                    varList.innerHTML += `
                        <label class="p-2.5 rounded-xl border border-slate-200 cursor-pointer flex items-center justify-between text-xs hover:border-amber-400">
                            <div>
                                <input type="radio" name="variant_choice" value="${v.id}" ${idx === 0 ? 'checked' : ''} onchange="updateVariantSelection(${v.id})" class="text-amber-500 focus:ring-amber-400 mr-1.5">
                                <span class="font-bold text-slate-800">${v.name}</span>
                            </div>
                            <span class="text-amber-600 font-semibold text-[11px]">${modText}</span>
                        </label>
                    `;
                });
            } else {
                varContainer.classList.add('hidden');
            }

            // Populate Addons
            const addContainer = document.getElementById('modalAddonsContainer');
            const addList = document.getElementById('modalAddonsList');
            addList.innerHTML = '';

            if (product.active_addons && product.active_addons.length > 0) {
                addContainer.classList.remove('hidden');
                product.active_addons.forEach(a => {
                    addList.innerHTML += `
                        <label class="p-2 rounded-xl border border-slate-200 cursor-pointer flex items-center justify-between text-xs hover:border-amber-400">
                            <div>
                                <input type="checkbox" value="${a.id}" onchange="toggleAddonSelection(${a.id}, this.checked)" class="text-amber-500 rounded focus:ring-amber-400 mr-2">
                                <span class="text-slate-800 font-medium">${a.name}</span>
                            </div>
                            <span class="text-amber-600 font-bold">+$${parseFloat(a.price).toFixed(2)}</span>
                        </label>
                    `;
                });
            } else {
                addContainer.classList.add('hidden');
            }

            updateModalPrice();
            document.getElementById('customizationModal').classList.remove('hidden');
        }

        function closeCustomizationModal() {
            document.getElementById('customizationModal').classList.add('hidden');
        }

        function updateVariantSelection(variantId) {
            selectedVariantId = variantId;
            updateModalPrice();
        }

        function toggleAddonSelection(addonId, checked) {
            if (checked) {
                if (!selectedAddonIds.includes(addonId)) selectedAddonIds.push(addonId);
            } else {
                selectedAddonIds = selectedAddonIds.filter(id => id !== addonId);
            }
            updateModalPrice();
        }

        function updateModalPrice() {
            if (!currentModalProduct) return;
            let price = parseFloat(currentModalProduct.base_price);

            if (selectedVariantId && currentModalProduct.active_variants) {
                const v = currentModalProduct.active_variants.find(x => x.id === selectedVariantId);
                if (v) price += parseFloat(v.price_modifier);
            }

            if (selectedAddonIds.length > 0 && currentModalProduct.active_addons) {
                selectedAddonIds.forEach(aId => {
                    const a = currentModalProduct.active_addons.find(x => x.id === aId);
                    if (a) price += parseFloat(a.price);
                });
            }

            document.getElementById('modalTotalPrice').innerText = '$' + price.toFixed(2);
        }

        function addToCartDirect(product) {
            cart.push({
                product_id: product.id,
                product_name: product.name,
                variant_id: null,
                variant_name: null,
                addon_ids: [],
                addons: [],
                unit_price: parseFloat(product.base_price),
                quantity: 1,
                notes: '',
            });
            renderCart();
        }

        function confirmAddToCart() {
            if (!currentModalProduct) return;

            let unitPrice = parseFloat(currentModalProduct.base_price);
            let variantName = null;
            let chosenAddons = [];

            if (selectedVariantId && currentModalProduct.active_variants) {
                const v = currentModalProduct.active_variants.find(x => x.id === selectedVariantId);
                if (v) {
                    unitPrice += parseFloat(v.price_modifier);
                    variantName = v.name;
                }
            }

            if (selectedAddonIds.length > 0 && currentModalProduct.active_addons) {
                selectedAddonIds.forEach(aId => {
                    const a = currentModalProduct.active_addons.find(x => x.id === aId);
                    if (a) {
                        unitPrice += parseFloat(a.price);
                        chosenAddons.push({ id: a.id, name: a.name, price: parseFloat(a.price) });
                    }
                });
            }

            cart.push({
                product_id: currentModalProduct.id,
                product_name: currentModalProduct.name,
                variant_id: selectedVariantId,
                variant_name: variantName,
                addon_ids: [...selectedAddonIds],
                addons: chosenAddons,
                unit_price: unitPrice,
                quantity: 1,
                notes: document.getElementById('modalItemNote').value.trim(),
            });

            closeCustomizationModal();
            renderCart();
        }

        // Cart Rendering & Quantity Manipulation
        function renderCart() {
            const list = document.getElementById('cartItemsList');
            const emptyMsg = document.getElementById('emptyCartMessage');

            if (cart.length === 0) {
                list.innerHTML = '';
                list.appendChild(emptyMsg);
                emptyMsg.style.display = 'block';
                document.getElementById('btnCheckout').disabled = true;
                updateSummaryDisplay(0, 0, 0, 0);
                return;
            }

            emptyMsg.style.display = 'none';
            list.innerHTML = '';

            cart.forEach((item, index) => {
                let addonsHtml = '';
                if (item.addons && item.addons.length > 0) {
                    addonsHtml = item.addons.map(a => `<span class="text-[10px] text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded mr-1">+${a.name}</span>`).join('');
                }

                const itemRow = document.createElement('div');
                itemRow.className = 'p-3 rounded-xl border border-slate-200 bg-white shadow-xs';
                itemRow.innerHTML = `
                    <div class="flex items-start justify-between mb-1.5">
                        <div class="pr-2">
                            <span class="font-bold text-xs text-slate-900 block leading-tight">${item.product_name}</span>
                            ${item.variant_name ? `<span class="text-[11px] text-slate-500 font-medium">${item.variant_name}</span>` : ''}
                        </div>
                        <span class="font-extrabold text-xs text-slate-900">$${(item.unit_price * item.quantity).toFixed(2)}</span>
                    </div>

                    ${addonsHtml ? `<div class="mb-1.5 flex flex-wrap gap-1">${addonsHtml}</div>` : ''}

                    <div class="flex items-center justify-between pt-1 text-xs">
                        <div class="flex items-center space-x-1.5">
                            <button onclick="adjustQuantity(${index}, -1)" class="w-6 h-6 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center">-</button>
                            <span class="w-6 text-center font-bold text-slate-900">${item.quantity}</span>
                            <button onclick="adjustQuantity(${index}, 1)" class="w-6 h-6 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center">+</button>
                        </div>
                        <button onclick="removeItem(${index})" class="text-[11px] text-rose-500 hover:underline">Remove</button>
                    </div>
                `;
                list.appendChild(itemRow);
            });

            document.getElementById('btnCheckout').disabled = false;
            fetchServerPricing();
        }

        function adjustQuantity(index, delta) {
            cart[index].quantity += delta;
            if (cart[index].quantity <= 0) {
                cart.splice(index, 1);
            }
            renderCart();
        }

        function removeItem(index) {
            cart.splice(index, 1);
            renderCart();
        }

        function clearCart() {
            cart = [];
            renderCart();
        }

        function cartUpdated() {
            fetchServerPricing();
        }

        // Server-Side Recalculation
        async function fetchServerPricing() {
            if (cart.length === 0) return;

            const promoId = document.getElementById('orderPromotionId').value;
            const customerId = document.getElementById('orderCustomerId').value;

            try {
                const response = await fetch("{{ route('pos.calculate') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        items: cart,
                        promotion_id: promoId || null,
                        customer_id: customerId || null,
                    })
                });

                const data = await response.json();
                if (data.success) {
                    calculatedTotals = data.data;
                    updateSummaryDisplay(
                        calculatedTotals.subtotal,
                        calculatedTotals.discount_amount,
                        calculatedTotals.tax_amount,
                        calculatedTotals.total_amount
                    );
                }
            } catch (err) {
                console.error("Pricing calculation error:", err);
            }
        }

        function updateSummaryDisplay(subtotal, discount, tax, total) {
            document.getElementById('cartSubtotal').innerText = '$' + subtotal.toFixed(2);
            document.getElementById('cartDiscount').innerText = '-$' + discount.toFixed(2);
            document.getElementById('cartTax').innerText = '$' + tax.toFixed(2);
            document.getElementById('cartTotal').innerText = '$' + total.toFixed(2);
            document.getElementById('btnCheckoutTotal').innerText = '$' + total.toFixed(2);
        }

        // Payment Modal & Workflow
        function openPaymentModal() {
            if (cart.length === 0) return;
            document.getElementById('payModalAmountDue').innerText = '$' + calculatedTotals.total_amount.toFixed(2);
            document.getElementById('payModalRemaining').innerText = '$' + calculatedTotals.total_amount.toFixed(2);
            document.getElementById('cashReceivedInput').value = calculatedTotals.total_amount.toFixed(2);
            calculateCashChange();
            document.getElementById('paymentModal').classList.remove('hidden');
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }

        function selectPaymentMethod(method, btn) {
            currentPaymentMethod = method;
            document.querySelectorAll('.pay-method-tab').forEach(b => {
                b.classList.remove('active', 'bg-amber-500', 'text-white');
                b.classList.add('bg-slate-100', 'text-slate-700');
            });
            btn.classList.add('active', 'bg-amber-500', 'text-white');
            btn.classList.remove('bg-slate-100', 'text-slate-700');

            const cashSec = document.getElementById('payCashSection');
            const digSec = document.getElementById('payDigitalSection');

            if (method === 'cash') {
                cashSec.classList.remove('hidden');
                digSec.classList.add('hidden');
            } else {
                cashSec.classList.add('hidden');
                digSec.classList.remove('hidden');
                document.getElementById('digitalIcon').innerText = method === 'card' ? '💳' : '📱';
                document.getElementById('digitalTitle').innerText = method === 'card' ? 'Credit / Debit Card Terminal' : 'Simulated QR Wallet';
            }
        }

        function quickTender(amount) {
            if (amount === 'exact') {
                document.getElementById('cashReceivedInput').value = calculatedTotals.total_amount.toFixed(2);
            } else {
                document.getElementById('cashReceivedInput').value = parseFloat(amount).toFixed(2);
            }
            calculateCashChange();
        }

        function calculateCashChange() {
            const received = parseFloat(document.getElementById('cashReceivedInput').value) || 0;
            const due = calculatedTotals.total_amount;
            const change = Math.max(0, received - due);
            document.getElementById('cashChangeDisplay').innerText = '$' + change.toFixed(2);
        }

        // Submit Checkout via AJAX
        async function submitOrderCheckout() {
            const btn = document.getElementById('btnSubmitOrder');
            btn.disabled = true;
            btn.innerText = 'Processing Order...';

            const cashReceived = parseFloat(document.getElementById('cashReceivedInput').value) || calculatedTotals.total_amount;
            const digitalRef = document.getElementById('digitalRefInput')?.value || null;

            const payload = {
                order_type: activeOrderType,
                table_number: document.getElementById('orderTableNumber').value.trim() || null,
                customer_id: document.getElementById('orderCustomerId').value || null,
                promotion_id: document.getElementById('orderPromotionId').value || null,
                items: cart,
                payments: [{
                    method: currentPaymentMethod,
                    amount: calculatedTotals.total_amount,
                    cash_received: currentPaymentMethod === 'cash' ? cashReceived : null,
                    reference: digitalRef,
                }]
            };

            try {
                const response = await fetch("{{ route('pos.checkout') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                if (result.success) {
                    closePaymentModal();
                    document.getElementById('receiptModalOrderNumber').innerText = `Order #${result.order.order_number}`;
                    document.getElementById('receiptPrintLink').href = result.receipt_url;
                    document.getElementById('receiptModal').classList.remove('hidden');
                } else {
                    alert("Checkout Error: " + (result.message || "Could not process order."));
                }
            } catch (err) {
                alert("Network or Server error during checkout.");
                console.error(err);
            } finally {
                btn.disabled = false;
                btn.innerText = 'Confirm & Complete Order';
            }
        }

        function resetPosForNextOrder() {
            document.getElementById('receiptModal').classList.add('hidden');
            clearCart();
            document.getElementById('orderTableNumber').value = '';
            document.getElementById('orderCustomerId').value = '';
            document.getElementById('orderPromotionId').value = '';
        }
    </script>
</body>
</html>
