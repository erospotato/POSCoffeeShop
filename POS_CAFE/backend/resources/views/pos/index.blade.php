<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS Terminal - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .thermal-receipt {
            font-family: 'JetBrains Mono', 'Courier New', Courier, monospace;
            background: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .thermal-receipt::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 0;
            right: 0;
            height: 8px;
            background: radial-gradient(circle, transparent, transparent 50%, #ffffff 50%, #ffffff 100% );
            background-size: 16px 16px;
        }
        /* Custom smooth scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-100 overflow-hidden flex flex-col select-none">
    <!-- Top Header Bar -->
    <header class="h-14 bg-slate-900 text-white flex items-center justify-between px-6 shrink-0 shadow-md z-30">
        <div class="flex items-center space-x-4">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-slate-300 hover:text-white transition text-xs font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Dashboard</span>
            </a>
            <div class="h-4 w-px bg-slate-700"></div>
            <div class="flex items-center space-x-2.5">
                <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="h-8 w-auto">
                <h1 class="text-sm font-black tracking-tight text-white">Veloura Café POS</h1>
            </div>
        </div>

        <div class="flex items-center space-x-3 text-xs">
            @if($activeShift)
                <div class="flex items-center space-x-2 bg-slate-800 px-3 py-1.5 rounded-xl border border-slate-700">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="font-extrabold text-slate-100">{{ $activeShift->cashier_station ?? 'Cashier 1' }}</span>
                    <span class="text-amber-400 font-semibold">({{ $activeShift->shift_period ?? 'Morning' }})</span>
                    <span class="text-slate-400">• Exp: ${{ number_format($activeShift->expected_cash, 2) }}</span>
                    <button type="button" onclick="openEndShiftModal()" class="ml-1.5 px-2 py-0.5 bg-rose-900/80 hover:bg-rose-800 text-rose-200 border border-rose-700 rounded-md text-[10px] font-bold transition">
                        End Shift
                    </button>
                </div>
            @else
                <div class="flex items-center space-x-2 bg-amber-950/80 text-amber-300 px-3 py-1.5 rounded-xl border border-amber-800">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <span class="font-bold">No Active Shift</span>
                    <button type="button" onclick="openStartShiftModal()" class="ml-1.5 px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black rounded-lg text-[10px] shadow-sm transition">
                        Start Shift
                    </button>
                </div>
            @endif

            <span class="text-slate-400 font-medium hidden md:inline">{{ Auth::user()->name }} ({{ Auth::user()->role->name }})</span>
            <div id="liveClock" class="font-mono text-amber-400 font-bold bg-slate-800 px-2.5 py-1 rounded-md"></div>
        </div>
    </header>

    <!-- Main POS Interface Grid -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Left Column: Catalog, Categories & Search -->
        <div class="flex-1 flex flex-col p-4 overflow-hidden border-r border-slate-200 bg-slate-100">
            <!-- Search & Filter Bar -->
            <div class="flex items-center space-x-3 mb-3 shrink-0">
                <div class="flex-1 relative">
                    <input type="text" id="posSearchInput" placeholder="Search coffee, drinks, pastries by name or SKU..." class="w-full px-4 py-2 bg-white border border-slate-300 rounded-xl text-xs focus:ring-amber-500 focus:border-amber-500 shadow-xs">
                </div>
            </div>

            <!-- Category Filter Pills -->
            <div class="flex items-center space-x-2 pb-3 shrink-0 overflow-x-auto" id="categoryFilterContainer">
                <button onclick="filterCategory('all', this)" class="category-btn active px-4 py-2 rounded-xl text-xs font-bold bg-amber-500 text-white shadow-sm transition">
                    All Menu Items ({{ $allProducts->count() }})
                </button>
                @foreach($categories as $category)
                    <button onclick="filterCategory('{{ $category->id }}', this)" class="category-btn px-4 py-2 rounded-xl text-xs font-semibold bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 transition shadow-xs whitespace-nowrap">
                        {{ $category->name }} ({{ $category->products->count() }})
                    </button>
                @endforeach
            </div>

            <!-- Realistic Product Cards Grid (Real Photos, No Emojis) -->
            <div class="flex-1 overflow-y-auto pr-1">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5" id="productGrid">
                    @foreach($allProducts as $product)
                        <div class="product-card group bg-white p-3 rounded-2xl border border-slate-200 hover:border-amber-400 hover:shadow-md transition cursor-pointer flex flex-col justify-between"
                             data-category-id="{{ $product->category_id }}"
                             data-name="{{ strtolower($product->name) }}"
                             data-sku="{{ strtolower($product->sku) }}"
                             onclick="selectProductById({{ $product->id }})">
                            <div>
                                <!-- Real Drink / Pastry Photography -->
                                <div class="w-full h-28 rounded-xl overflow-hidden bg-slate-100 mb-2 relative group-hover:shadow-xs transition">
                                    <img src="{{ $product->image_path ?: 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=600&q=80' }}"
                                         alt="{{ $product->name }}"
                                         loading="lazy"
                                         class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                         onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=600&q=80';">
                                    <span class="absolute bottom-1.5 left-1.5 bg-slate-900/80 backdrop-blur-xs text-white text-[9px] font-semibold px-2 py-0.5 rounded">
                                        {{ $product->category->name }}
                                    </span>
                                </div>
                                <h3 class="font-bold text-slate-900 text-xs leading-snug line-clamp-2">{{ $product->name }}</h3>
                                <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $product->sku }}</p>
                            </div>
                            <div class="mt-2.5 flex items-center justify-between pt-2 border-t border-slate-100">
                                <span class="font-extrabold text-amber-600 text-sm">${{ number_format($product->base_price, 2) }}</span>
                                <button type="button"
                                        onclick="event.stopPropagation(); quickAddProductById({{ $product->id }});"
                                        title="Quick Add to Order"
                                        class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-500 hover:text-white text-amber-600 font-black flex items-center justify-center text-sm transition shadow-xs active:scale-95">
                                    +
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Column: Interactive Order & Checkout Panel (Full Scrollable Sidebar, Resizable & Collapsible) -->
        <div id="posSidebar" class="w-[420px] bg-white flex flex-col border-l border-slate-200 shadow-xl shrink-0 h-full overflow-y-auto transition-all duration-200 relative">
            <!-- Sidebar Header Controls (Resize & Collapse) -->
            <div class="px-3.5 py-2 border-b border-slate-200 flex items-center justify-between bg-slate-100/90 shrink-0">
                <div class="flex items-center space-x-2">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-600">Ticket</span>
                    <!-- Size toggle -->
                    <div class="flex items-center space-x-0.5 bg-slate-200/90 rounded-lg p-0.5 text-[9px] font-bold">
                        <button type="button" onclick="setSidebarWidth('compact')" class="sidebar-size-btn px-1.5 py-0.5 rounded text-slate-600 hover:text-slate-900 transition" title="Compact (340px)">340px</button>
                        <button type="button" onclick="setSidebarWidth('normal')" class="sidebar-size-btn active px-1.5 py-0.5 rounded bg-white text-slate-900 shadow-2xs font-extrabold" title="Standard (420px)">420px</button>
                        <button type="button" onclick="setSidebarWidth('wide')" class="sidebar-size-btn px-1.5 py-0.5 rounded text-slate-600 hover:text-slate-900 transition" title="Wide (520px)">520px</button>
                    </div>
                </div>
                <!-- Collapse Button -->
                <button type="button" onclick="toggleSidebarCollapse()" class="px-2 py-1 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-200 text-xs font-bold transition flex items-center space-x-1" title="Close / Hide Sidebar">
                    <span>Close</span>
                    <span>&times;</span>
                </button>
            </div>

            <!-- Order Parameters -->
            <div class="p-3.5 border-b border-slate-200 space-y-2.5 bg-slate-50/70 shrink-0">
                <!-- Order Type Selector -->
                <div class="grid grid-cols-3 gap-1 bg-slate-200 p-1 rounded-xl text-xs font-bold">
                    <button type="button" onclick="setOrderType('dine_in', this)" class="order-type-btn active py-1.5 rounded-lg bg-white text-slate-900 shadow-xs text-center">Dine-in</button>
                    <button type="button" onclick="setOrderType('takeaway', this)" class="order-type-btn py-1.5 rounded-lg text-slate-600 hover:text-slate-900 text-center">Takeaway</button>
                    <button type="button" onclick="setOrderType('delivery', this)" class="order-type-btn py-1.5 rounded-lg text-slate-600 hover:text-slate-900 text-center">Delivery</button>
                </div>

                <!-- Table Number -->
                <div id="tableNumberSection">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Table Number</label>
                    <input type="text" id="orderTableNumber" placeholder="e.g. Table 4" class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs">
                </div>

                <!-- Loyalty Program & Customer Card Section (Select / Enter / Scan) -->
                <div class="p-2.5 bg-white rounded-xl border border-amber-200 shadow-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-700 flex items-center space-x-1">
                            <span>Customer Loyalty Program</span>
                        </span>
                        <button type="button" onclick="openQuickNewCustomerModal()" class="text-[10px] font-bold text-amber-600 hover:text-amber-800 underline">
                            + New Member
                        </button>
                    </div>

                    <!-- Search / Scan Input (Suggests members on click/focus) -->
                    <div class="relative">
                        <input type="text" id="customerSearchScanInput"
                               placeholder="Scan card or search member name / phone..."
                               onfocus="onCustomerSearchFocus()"
                               onclick="onCustomerSearchFocus()"
                               oninput="onCustomerSearchInput(this.value)"
                               onkeydown="handleCustomerScanKey(event)"
                               class="w-full pl-3 pr-14 py-1.5 bg-amber-50/40 border border-amber-300 rounded-lg text-xs focus:ring-amber-500 focus:border-amber-500">
                        <button type="button" onclick="triggerScanSimulation()" title="Simulate Barcode / Loyalty Card Scan" class="absolute right-2 top-1.5 text-[10px] font-bold text-amber-700 hover:text-amber-900 bg-amber-100 hover:bg-amber-200 px-2 py-0.5 rounded transition">
                            Scan
                        </button>

                        <!-- Autocomplete Suggestions Dropdown -->
                        <div id="customerSuggestionsList" class="hidden absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg z-30 max-h-56 overflow-y-auto"></div>
                    </div>

                    <!-- Active Customer Profile Card (Shown when selected) -->
                    <div id="activeCustomerCard" class="hidden p-2 rounded-lg bg-amber-50 border border-amber-200 text-xs">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="font-bold text-slate-900 block" id="activeCustomerName">Customer Name</span>
                                <span class="text-[10px] text-slate-500 font-mono" id="activeCustomerPhone">012999001</span>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-200 text-amber-900" id="activeCustomerPointsBadge">
                                    0 pts
                                </span>
                                <div class="flex items-center justify-end space-x-2 mt-0.5">
                                    <a id="activeCustomerHistoryLink" href="#" target="_blank" class="text-[10px] font-bold text-amber-700 hover:text-amber-900 hover:underline">History</a>
                                    <button type="button" onclick="detachCustomer()" class="text-[10px] text-rose-500 hover:underline">Remove</button>
                                </div>
                            </div>
                        </div>

                        <!-- Loyalty Reward Redemption Options -->
                        <div class="mt-2 pt-2 border-t border-amber-200/80">
                            <label class="block text-[10px] font-bold text-amber-900 mb-1">Redeem Loyalty Points Reward:</label>
                            <select id="loyaltyRewardSelect" onchange="onLoyaltyRewardSelected()" class="w-full text-xs px-2 py-1 bg-white border border-amber-300 rounded-md">
                                <option value="">Do not redeem rewards (Earn points)</option>
                                @foreach($loyaltyRewards as $reward)
                                    <option value="{{ $reward->id }}" data-points="{{ $reward->points_required }}" data-desc="{{ $reward->name }}">
                                        {{ $reward->name }} (Cost: {{ $reward->points_required }} pts)
                                    </option>
                                @endforeach
                            </select>
                            <p id="loyaltyRewardNotice" class="text-[10px] text-amber-800 font-semibold mt-1 hidden"></p>
                        </div>
                    </div>

                    <input type="hidden" id="selectedCustomerId" value="">
                    <input type="hidden" id="selectedLoyaltyRewardId" value="">
                </div>
            </div>

            <!-- Cart Items List Container -->
            <div class="flex-1 p-3.5 space-y-2 overflow-y-auto min-h-[160px]" id="cartScrollContainer">
                <div class="py-16 text-center text-slate-400 text-xs" id="emptyCartMessage">
                    <div class="w-12 h-12 mx-auto mb-2 text-slate-300 flex items-center justify-center">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    Your order ticket is currently empty.<br>Click any drink or pastry to add to order.
                </div>
                <div id="cartItemsList" class="space-y-2"></div>
            </div>

            <!-- Promotions & Discounts (During Checkout) -->
            <div class="px-3.5 py-2.5 bg-slate-50 border-t border-slate-200 space-y-2 shrink-0">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Apply Promotion / Promo Code</span>
                    <button type="button" onclick="togglePromoList()" class="text-[10px] text-amber-600 font-bold hover:underline">
                        View Active Deals
                    </button>
                </div>

                <div class="flex items-center space-x-1.5">
                    <input type="text" id="promoCodeInput" placeholder="Enter Promo Code (e.g. COFFEE15)" class="flex-1 px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-mono uppercase">
                    <button type="button" onclick="applyPromoCodeManual()" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-lg text-xs transition">
                        Apply
                    </button>
                </div>

                <!-- Active Promo Badges / Clickable deals -->
                <div id="promoChipsContainer" class="flex flex-wrap gap-1 pt-1">
                    @foreach($promotions as $promo)
                        <button type="button" onclick="selectPromoDirect({{ $promo->id }}, '{{ $promo->code }}')" class="promo-chip text-[10px] font-bold px-2 py-0.5 rounded-md bg-white border border-slate-200 hover:border-amber-400 text-slate-700 transition flex items-center space-x-1" data-promo-id="{{ $promo->id }}">
                            <span class="font-mono text-amber-700 font-bold">{{ $promo->code }}</span>
                            <span class="text-slate-400">({{ $promo->type === 'percentage' ? $promo->value.'%' : '$'.$promo->value }} off)</span>
                        </button>
                    @endforeach
                </div>

                <!-- Applied Promo Notice -->
                <div id="appliedPromoBadge" class="hidden p-1.5 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs text-emerald-800">
                    <div class="flex items-center space-x-1">
                        <span>✓ Promo:</span>
                        <span class="font-bold" id="appliedPromoName">15% Off</span>
                    </div>
                    <button type="button" onclick="removeAppliedPromo()" class="text-emerald-700 font-bold hover:underline text-[10px]">✕ Remove</button>
                </div>

                <input type="hidden" id="selectedPromotionId" value="">
            </div>

            <!-- Cart Summary & Checkout Box (TAX REMOVED, Scrollable/Sticky) -->
            <div class="p-3.5 border-t border-slate-200 bg-white space-y-2 text-xs shrink-0 shadow-md">
                <div class="flex justify-between text-slate-500">
                    <span>Subtotal</span>
                    <span id="cartSubtotal" class="font-semibold text-slate-800 font-mono">$0.00</span>
                </div>
                <div class="flex justify-between text-emerald-600 hidden" id="discountRow">
                    <span>Discounts & Rewards</span>
                    <span id="cartDiscount" class="font-bold font-mono">-$0.00</span>
                </div>

                <!-- NOTE: Tax row is completely removed as requested -->

                <div class="pt-2 border-t border-slate-200 flex justify-between items-baseline">
                    <span class="text-sm font-extrabold text-slate-900">Total Due</span>
                    <span id="cartTotal" class="text-2xl font-black text-amber-600 font-mono">$0.00</span>
                </div>

                <div class="grid grid-cols-4 gap-2 pt-1">
                    <button onclick="clearCart()" class="col-span-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition text-xs">
                        Clear
                    </button>
                    <button onclick="openPaymentModal()" id="btnCheckout" class="col-span-3 py-3 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-extrabold rounded-xl shadow-md transition text-sm flex items-center justify-center space-x-1.5" disabled>
                        <span>Checkout / Pay</span>
                        <span id="btnCheckoutTotal" class="font-mono">$0.00</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Expand Sidebar Button (when sidebar is collapsed) -->
    <button id="floatingOpenSidebarBtn" type="button" onclick="toggleSidebarCollapse()" class="hidden fixed right-6 bottom-6 z-40 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black px-4 py-3 rounded-2xl shadow-2xl flex items-center space-x-2.5 transition active:scale-95 border-2 border-white">
        <svg class="w-5 h-5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        <span class="text-xs font-black">View Order</span>
        <span id="floatingCartCount" class="bg-slate-900 text-white text-xs px-2 py-0.5 rounded-full font-mono font-bold">0</span>
        <span id="floatingCartTotal" class="font-mono text-sm font-black">$0.00</span>
    </button>

    <!-- Product Customization Modal (Photo, Variants & Addons) -->
    <div id="customizationModal" onclick="if(event.target === this) closeCustomizationModal()" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
        <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl flex flex-col">
            <!-- Modal Header with Real Photo -->
            <div class="h-36 w-full relative bg-slate-100">
                <img id="modalProductImage" src="" alt="Product" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/30 to-transparent"></div>
                <button onclick="closeCustomizationModal()" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-slate-900/70 hover:bg-slate-900 text-white flex items-center justify-center font-bold text-lg">&times;</button>
                <div class="absolute bottom-3 left-4 right-4 text-white">
                    <span class="text-[10px] uppercase font-bold text-amber-400" id="modalProductCategory">Coffee</span>
                    <h3 class="text-base font-extrabold leading-tight" id="modalProductName">Product Name</h3>
                </div>
            </div>

            <div class="p-5 overflow-y-auto space-y-4 max-h-[60vh]">
                <p class="text-xs text-slate-500" id="modalProductDescription"></p>

                <!-- Variants / Sizes -->
                <div id="modalVariantsContainer">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Select Size / Variant</label>
                    <div class="grid grid-cols-2 gap-2" id="modalVariantsList"></div>
                </div>

                <!-- Addons -->
                <div id="modalAddonsContainer">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Add-ons & Syrups</label>
                    <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1" id="modalAddonsList"></div>
                </div>

                <!-- Item Preparation Note -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Preparation Note (Optional)</label>
                    <input type="text" id="modalItemNote" placeholder="e.g. Less sugar, extra hot, oat milk" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Item Price</span>
                    <span class="text-xl font-black text-amber-600 font-mono" id="modalTotalPrice">$0.00</span>
                </div>
                <button type="button" onclick="confirmAddToCart()" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-extrabold rounded-xl shadow-md text-xs">
                    Add to Cart
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Temperature / Variant Choice Modal (Prompt for Hot or Iced) -->
    <div id="quickVariantModal" onclick="if(event.target === this) closeQuickVariantModal()" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
        <div class="bg-white rounded-3xl max-w-sm w-full overflow-hidden shadow-2xl p-5 border border-slate-100 flex flex-col space-y-4">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <img id="quickVarModalImage" src="" alt="Product" class="w-12 h-12 rounded-xl object-cover bg-slate-100 shadow-xs">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-amber-500" id="quickVarModalCategory">Coffee</span>
                        <h3 class="text-sm font-extrabold text-slate-900 leading-tight" id="quickVarModalName">Classic Americano</h3>
                    </div>
                </div>
                <button type="button" onclick="closeQuickVariantModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-base transition">&times;</button>
            </div>

            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Select Style / Temperature</label>
                <div class="grid grid-cols-1 gap-2.5" id="quickVariantTiles">
                    <!-- Populated dynamically: Hot or Iced tiles -->
                </div>
            </div>

            <!-- Footer options -->
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                <button type="button" onclick="openFullCustomizationFromQuick()" class="text-amber-600 font-bold hover:underline flex items-center space-x-1">
                    <span>Custom Syrups & Notes</span>
                </button>
                <button type="button" onclick="closeQuickVariantModal()" class="text-slate-400 hover:text-slate-600 font-semibold px-2 py-1">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Realistic POS Touch Payment Terminal Modal -->
    <div id="paymentModal" onclick="if(event.target === this) closePaymentModal()" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-xl w-full overflow-hidden shadow-2xl flex flex-col">
            <!-- Terminal Header -->
            <div class="bg-slate-900 text-white p-5 flex items-center justify-between">
                <div>
                    <span class="text-[10px] uppercase font-bold tracking-widest text-amber-400 block">Veloura Café • Checkout Terminal</span>
                    <h3 class="text-base font-black">Complete Payment</h3>
                </div>
                <button onclick="closePaymentModal()" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center font-bold text-base">&times;</button>
            </div>

            <!-- Amount Due Banner -->
            <div class="bg-slate-950 p-5 text-white flex items-center justify-between border-b border-slate-800">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Total Amount Due</span>
                    <div class="text-3xl font-black text-amber-400 font-mono tracking-tight" id="payModalAmountDue">$0.00</div>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider" id="payModalCustomerLabel">Customer</span>
                    <div class="text-sm font-bold text-emerald-400" id="payModalCustomerName">Walk-in</div>
                </div>
            </div>

            <!-- Payment Method Hardware Selector Tabs -->
            <div class="p-5 space-y-4">
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="selectPaymentMethod('cash', this)" class="pay-method-tab active py-3 px-2 rounded-2xl text-xs font-black bg-amber-500 text-white shadow-md flex flex-col items-center justify-center space-y-1 transition">
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Cash</span>
                    </button>
                    <button type="button" onclick="selectPaymentMethod('card', this)" class="pay-method-tab py-3 px-2 rounded-2xl text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 flex flex-col items-center justify-center space-y-1 transition">
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>Credit / Debit Card</span>
                    </button>
                    <button type="button" onclick="selectPaymentMethod('qr_code', this)" class="pay-method-tab py-3 px-2 rounded-2xl text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 flex flex-col items-center justify-center space-y-1 transition">
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>QR Code / Mobile</span>
                    </button>
                </div>

                <!-- 1. Cash Payment Section (with Quick Tender & Touch Numpad) -->
                <div id="payCashSection" class="space-y-3">
                    <div class="grid grid-cols-2 gap-3 items-center">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Cash Tendered ($)</label>
                            <input type="number" step="0.01" id="cashReceivedInput" oninput="calculateCashChange()" class="w-full text-2xl font-black text-slate-900 border-2 border-slate-300 rounded-xl px-3 py-2 font-mono">
                        </div>

                        <!-- Change Due High-Contrast Display -->
                        <div class="p-3 rounded-xl bg-emerald-50 border-2 border-emerald-300 flex flex-col justify-center">
                            <span class="text-[10px] font-black uppercase text-emerald-800">Change Due:</span>
                            <span class="text-2xl font-black text-emerald-700 font-mono" id="cashChangeDisplay">$0.00</span>
                        </div>
                    </div>

                    <!-- Quick Tender Cash Bill Buttons -->
                    <div class="grid grid-cols-6 gap-1.5">
                        <button type="button" onclick="quickTender('exact')" class="py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-black text-slate-800">Exact</button>
                        <button type="button" onclick="quickTender(5)" class="py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-800">$5</button>
                        <button type="button" onclick="quickTender(10)" class="py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-800">$10</button>
                        <button type="button" onclick="quickTender(20)" class="py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-800">$20</button>
                        <button type="button" onclick="quickTender(50)" class="py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-800">$50</button>
                        <button type="button" onclick="quickTender(100)" class="py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-800">$100</button>
                    </div>

                    <!-- Touchscreen Numeric Keypad -->
                    <div class="grid grid-cols-4 gap-1.5 pt-1">
                        <button type="button" onclick="numpadInput('7')" class="py-2.5 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl text-base font-bold text-slate-800 shadow-xs">7</button>
                        <button type="button" onclick="numpadInput('8')" class="py-2.5 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl text-base font-bold text-slate-800 shadow-xs">8</button>
                        <button type="button" onclick="numpadInput('9')" class="py-2.5 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl text-base font-bold text-slate-800 shadow-xs">9</button>
                        <button type="button" onclick="numpadInput('clear')" class="py-2.5 bg-rose-50 border border-rose-200 hover:bg-rose-100 rounded-xl text-xs font-bold text-rose-700">Clear</button>

                        <button type="button" onclick="numpadInput('4')" class="py-2.5 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl text-base font-bold text-slate-800 shadow-xs">4</button>
                        <button type="button" onclick="numpadInput('5')" class="py-2.5 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl text-base font-bold text-slate-800 shadow-xs">5</button>
                        <button type="button" onclick="numpadInput('6')" class="py-2.5 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl text-base font-bold text-slate-800 shadow-xs">6</button>
                        <button type="button" onclick="numpadInput('backspace')" class="py-2.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700">⌫</button>

                        <button type="button" onclick="numpadInput('1')" class="py-2.5 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl text-base font-bold text-slate-800 shadow-xs">1</button>
                        <button type="button" onclick="numpadInput('2')" class="py-2.5 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl text-base font-bold text-slate-800 shadow-xs">2</button>
                        <button type="button" onclick="numpadInput('3')" class="py-2.5 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl text-base font-bold text-slate-800 shadow-xs">3</button>
                        <button type="button" onclick="numpadInput('00')" class="py-2.5 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl text-sm font-bold text-slate-800 shadow-xs">00</button>

                        <button type="button" onclick="numpadInput('0')" class="py-2.5 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl text-base font-bold text-slate-800 shadow-xs col-span-2">0</button>
                        <button type="button" onclick="numpadInput('.')" class="py-2.5 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl text-base font-bold text-slate-800 shadow-xs col-span-2">.</button>
                    </div>
                </div>

                <!-- 2. Realistic Card Terminal Simulator -->
                <div id="payCardSection" class="hidden space-y-3">
                    <div class="p-5 rounded-2xl bg-slate-900 text-white border border-slate-700 flex flex-col items-center justify-center text-center space-y-2">
                        <div class="w-12 h-12 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">EMV / Contactless Card Reader</span>
                        <p class="text-xs text-slate-300">Tap contactless card / Apple Pay, or insert chip card.</p>
                        <div class="flex items-center space-x-2 pt-2">
                            <span class="px-2 py-0.5 rounded bg-slate-800 text-[10px] font-bold text-slate-300">VISA</span>
                            <span class="px-2 py-0.5 rounded bg-slate-800 text-[10px] font-bold text-slate-300">MasterCard</span>
                            <span class="px-2 py-0.5 rounded bg-slate-800 text-[10px] font-bold text-slate-300">AMEX</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Terminal Auth Code / Ref</label>
                        <input type="text" id="cardAuthRef" value="CARD-AUTH-892147" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-mono">
                    </div>
                </div>

                <!-- 3. Realistic QR Code Payment Section -->
                <div id="payQrSection" class="hidden space-y-3 text-center">
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col items-center justify-center">
                        <span class="text-xs font-bold text-slate-800 mb-1">Scan to Pay via Mobile Banking / E-Wallet</span>
                        <div class="w-44 h-44 bg-white p-2 border-2 border-slate-900 rounded-xl shadow-xs flex items-center justify-center relative my-2">
                            <svg class="w-40 h-40" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="100" height="100" fill="white"/>
                                <rect x="5" y="5" width="30" height="30" fill="black"/>
                                <rect x="10" y="10" width="20" height="20" fill="white"/>
                                <rect x="15" y="15" width="10" height="10" fill="black"/>
                                <rect x="65" y="5" width="30" height="30" fill="black"/>
                                <rect x="70" y="10" width="20" height="20" fill="white"/>
                                <rect x="75" y="15" width="10" height="10" fill="black"/>
                                <rect x="5" y="65" width="30" height="30" fill="black"/>
                                <rect x="10" y="70" width="20" height="20" fill="white"/>
                                <rect x="15" y="75" width="10" height="10" fill="black"/>
                                <rect x="42" y="8" width="6" height="6" fill="black"/>
                                <rect x="52" y="8" width="6" height="6" fill="black"/>
                                <rect x="42" y="20" width="16" height="6" fill="black"/>
                                <rect x="12" y="42" width="6" height="16" fill="black"/>
                                <rect x="25" y="45" width="8" height="8" fill="black"/>
                                <rect x="42" y="42" width="16" height="16" fill="#D97706"/>
                                <rect x="65" y="42" width="8" height="8" fill="black"/>
                                <rect x="78" y="42" width="14" height="8" fill="black"/>
                                <rect x="65" y="55" width="16" height="8" fill="black"/>
                                <rect x="42" y="65" width="8" height="18" fill="black"/>
                                <rect x="55" y="75" width="15" height="8" fill="black"/>
                                <rect x="75" y="75" width="15" height="15" fill="black"/>
                            </svg>
                            <span class="absolute bottom-1 bg-amber-500 text-white font-mono text-[9px] font-bold px-1.5 py-0.5 rounded">Veloura Pay</span>
                        </div>
                        <span class="text-sm font-black text-amber-600 font-mono" id="qrAmountDisplay">$0.00</span>
                        <p class="text-[11px] text-slate-500 mt-1">PromptPay / KHQR / Apple Pay supported.</p>
                    </div>
                </div>
            </div>

            <!-- Modal Action Footer -->
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <button type="button" onclick="closePaymentModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-200 rounded-xl transition">
                    Back to Order
                </button>
                <button type="button" id="btnSubmitOrder" onclick="submitOrderCheckout()" class="px-7 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl shadow-lg text-sm transition flex items-center space-x-2">
                    <span id="btnSubmitOrderText">Confirm & Complete Order</span>
                    <span id="btnSubmitOrderTotal" class="font-mono">$0.00</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Thermal Customer Receipt Modal (Appears immediately upon Payment Confirmation) -->
    <div id="receiptModal" onclick="if(event.target === this) resetPosForNextOrder()" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-950/75 p-4 backdrop-blur-sm overflow-y-auto">
        <div class="max-w-sm w-full my-6 flex flex-col items-center relative">
            <button type="button" onclick="resetPosForNextOrder()" title="Close Receipt" class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-white font-black text-base flex items-center justify-center shadow-xl z-20 transition border border-slate-600">&times;</button>
            <!-- Authentic Thermal Paper Receipt -->
            <div class="thermal-receipt w-full p-6 text-slate-900 rounded-t-2xl border-t-4 border-amber-500">
                <div class="text-center pb-3 border-b border-dashed border-slate-300">
                    <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" class="h-10 w-auto mx-auto mb-1">
                    <h2 class="text-base font-black uppercase tracking-tight" id="receiptStoreName">Veloura Café</h2>
                    <p class="text-[10px] text-slate-500" id="receiptStoreAddress">No. 88 Heritage Blvd</p>
                    <p class="text-[10px] text-slate-500" id="receiptStorePhone">Tel: +855 23 888 999</p>
                </div>

                <div class="py-2.5 border-b border-dashed border-slate-300 text-[11px] space-y-0.5">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Order No:</span>
                        <span class="font-bold" id="receiptOrderNumber">ORD-0000</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Date/Time:</span>
                        <span id="receiptDateTime">20/10/2026 14:30:00</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Cashier:</span>
                        <span id="receiptCashier">Staff</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Type:</span>
                        <span class="font-bold" id="receiptOrderType">DINE IN</span>
                    </div>

                    <!-- Customer & Loyalty Summary on Receipt -->
                    <div id="receiptCustomerSection" class="hidden pt-1 mt-1 border-t border-dashed border-slate-200">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Member:</span>
                            <span class="font-bold text-amber-800" id="receiptCustomerName">Sophia</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Points Earned:</span>
                            <span class="font-bold text-emerald-600" id="receiptPointsEarned">+12 pts</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Points Balance:</span>
                            <span class="font-bold" id="receiptPointsBalance">162 pts</span>
                        </div>
                    </div>
                </div>

                <!-- Receipt Items Table Header -->
                <div class="py-1.5 border-b border-slate-900 text-[11px] font-bold flex justify-between uppercase">
                    <span class="flex-2">Item</span>
                    <span class="flex-1 text-center">Qty</span>
                    <span class="flex-1 text-right">Amount</span>
                </div>

                <!-- Receipt Items Rows -->
                <div class="py-2 space-y-1.5 text-[11px] border-b border-dashed border-slate-300" id="receiptItemsContainer"></div>

                <!-- Financial Totals (TAX REMOVED) -->
                <div class="py-2.5 border-b border-dashed border-slate-300 text-[11px] space-y-1">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span id="receiptSubtotal" class="font-mono">$0.00</span>
                    </div>
                    <div class="flex justify-between text-emerald-600 hidden" id="receiptDiscountRow">
                        <span>Discount / Loyalty</span>
                        <span id="receiptDiscount" class="font-mono font-bold">-$0.00</span>
                    </div>
                    <!-- Tax line omitted per specification -->
                    <div class="pt-1.5 flex justify-between text-base font-black text-slate-900 border-t border-slate-900">
                        <span>TOTAL PAID</span>
                        <span id="receiptTotal" class="font-mono">$0.00</span>
                    </div>
                </div>

                <!-- Payment Breakdown -->
                <div class="py-2 border-b border-dashed border-slate-300 text-[10px] space-y-0.5 text-slate-600" id="receiptPaymentDetails"></div>

                <!-- Barcode Simulation & Footer -->
                <div class="pt-3 text-center space-y-1">
                    <div class="h-8 flex items-center justify-center space-x-1 px-4 opacity-75">
                        <span class="h-full w-1 bg-black"></span>
                        <span class="h-full w-0.5 bg-black"></span>
                        <span class="h-full w-1.5 bg-black"></span>
                        <span class="h-full w-0.5 bg-black"></span>
                        <span class="h-full w-2 bg-black"></span>
                        <span class="h-full w-0.5 bg-black"></span>
                        <span class="h-full w-1 bg-black"></span>
                        <span class="h-full w-2 bg-black"></span>
                        <span class="h-full w-0.5 bg-black"></span>
                        <span class="h-full w-1 bg-black"></span>
                        <span class="h-full w-1.5 bg-black"></span>
                    </div>
                    <p class="text-[9px] font-mono text-slate-500" id="receiptBarcodeText">ORD-0000</p>
                    <p class="text-[10px] font-semibold text-slate-700 pt-1" id="receiptStoreFooter">Thank you for visiting Veloura Café!</p>
                </div>
            </div>

            <!-- Receipt Actions -->
            <div class="mt-4 flex items-center space-x-3 w-full">
                <a id="receiptPrintLink" href="#" target="_blank" class="flex-1 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-2xl text-xs transition text-center shadow-md flex items-center justify-center">
                    <span>Print Receipt</span>
                </a>
                <button onclick="resetPosForNextOrder()" class="flex-1 py-3 bg-amber-500 hover:bg-amber-600 text-white font-extrabold rounded-2xl text-xs transition text-center shadow-md flex items-center justify-center">
                    <span>Next Order</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Enroll Customer Modal -->
    <div id="quickCustomerModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
        <div class="bg-white rounded-3xl max-w-sm w-full p-5 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                <h3 class="text-sm font-bold text-slate-900">Enroll Loyalty Member</h3>
                <button onclick="closeQuickNewCustomerModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Customer Full Name *</label>
                    <input type="text" id="quickCustomerName" placeholder="e.g. Alex Robinson" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Phone Number (Loyalty ID) *</label>
                    <input type="text" id="quickCustomerPhone" placeholder="e.g. 012999099" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-mono">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Email Address (Optional)</label>
                    <input type="email" id="quickCustomerEmail" placeholder="alex@example.com" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>
            </div>
            <div class="pt-4 mt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeQuickNewCustomerModal()" class="px-3 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-100 rounded-xl">Cancel</button>
                <button type="button" onclick="submitQuickCustomerEnrollment()" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-xs">Enroll & Attach</button>
            </div>
        </div>
    </div>

    <!-- Start Shift Modal (Cashier 1/2/3, Morning/Evening Shift) -->
    <div id="startShiftModal" onclick="if(event.target === this) closeStartShiftModal()" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 flex flex-col space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <h3 class="text-sm font-extrabold text-slate-900">Start Cashier Shift</h3>
                </div>
                <button type="button" onclick="closeStartShiftModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-base">&times;</button>
            </div>

            <form method="POST" action="{{ route('shifts.open') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Cashier Register / Station *</label>
                    <select name="cashier_station" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-bold bg-white">
                        <option value="Cashier 1">Cashier 1 (Main Register)</option>
                        <option value="Cashier 2">Cashier 2 (Express Register)</option>
                        <option value="Cashier 3">Cashier 3 (Terrace / Drive-thru)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Shift Period *</label>
                    <select name="shift_period" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-bold bg-white">
                        <option value="Morning">Morning Shift (06:00 - 14:00)</option>
                        <option value="Evening">Evening Shift (14:00 - 22:00)</option>
                        <option value="Full Day">Full Day Shift</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Opening Cash Float ($) *</label>
                    <input type="number" step="0.01" name="opening_cash" value="100.00" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-mono font-bold">
                </div>

                <div class="pt-2 flex items-center space-x-2">
                    <button type="button" onclick="closeStartShiftModal()" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-xl text-xs shadow-md transition">Open Shift</button>
                </div>
            </form>
        </div>
    </div>

    @if($activeShift)
    <!-- End Shift Modal -->
    <div id="endShiftModal" onclick="if(event.target === this) closeEndShiftModal()" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 flex flex-col space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                    <h3 class="text-sm font-extrabold text-slate-900">End Cashier Shift #{{ $activeShift->id }}</h3>
                </div>
                <button type="button" onclick="closeEndShiftModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-base">&times;</button>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1 font-sans">
                <div class="flex justify-between"><span class="text-slate-500">Station / Period:</span><span class="font-bold text-slate-800">{{ $activeShift->cashier_station ?? 'Cashier 1' }} ({{ $activeShift->shift_period ?? 'Morning' }})</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Expected Cash:</span><span class="font-mono font-bold text-amber-600">${{ number_format($activeShift->expected_cash, 2) }}</span></div>
            </div>

            <form method="POST" action="{{ route('shifts.close', $activeShift->id) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Actual Cash Counted ($) *</label>
                    <input type="number" step="0.01" name="actual_cash" value="{{ number_format($activeShift->expected_cash, 2, '.', '') }}" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-mono font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Handover Notes (Optional)</label>
                    <textarea name="notes" rows="2" placeholder="e.g. Register balanced, handover to evening shift..." class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl"></textarea>
                </div>

                <div class="pt-2 flex items-center space-x-2">
                    <button type="button" onclick="closeEndShiftModal()" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold rounded-xl text-xs shadow-md transition">Confirm & Close Shift</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- POS JavaScript Implementation -->
    <script>
        // Global POS state
        let cart = [];
        let activeOrderType = 'dine_in';
        let currentModalProduct = null;
        let selectedVariantId = null;
        let selectedAddonIds = [];
        let calculatedTotals = { subtotal: 0, discount_amount: 0, tax_amount: 0, total_amount: 0 };
        let currentPaymentMethod = 'cash';
        let selectedCustomer = null;
        let allCustomers = @json($customers);
        let allPromotions = @json($promotions);
        let allLoyaltyRewards = @json($loyaltyRewards);
        let allProductsCatalog = @json($allProducts->keyBy('id'));
        let cashInputFresh = true;

        // Digital Clock
        function updateClock() {
            const el = document.getElementById('liveClock');
            if (el) el.innerText = new Date().toLocaleTimeString();
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Search Catalog
        document.getElementById('posSearchInput')?.addEventListener('input', function (e) {
            const term = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.product-card').forEach(card => {
                const name = card.dataset.name;
                const sku = card.dataset.sku;
                card.style.display = (name.includes(term) || sku.includes(term)) ? 'flex' : 'none';
            });
        });

        // Filter Categories
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
                b.classList.remove('active', 'bg-white', 'text-slate-900', 'shadow-xs');
                b.classList.add('text-slate-600');
            });
            btn.classList.add('active', 'bg-white', 'text-slate-900', 'shadow-xs');
            btn.classList.remove('text-slate-600');

            const tableSection = document.getElementById('tableNumberSection');
            if (type === 'dine_in') {
                tableSection.classList.remove('hidden');
            } else {
                tableSection.classList.add('hidden');
            }
        }

        // Helper: Check if two arrays of IDs match
        function arraysMatch(a, b) {
            const arr1 = a || [];
            const arr2 = b || [];
            if (arr1.length !== arr2.length) return false;
            const s1 = [...arr1].sort();
            const s2 = [...arr2].sort();
            return s1.every((val, idx) => val === s2[idx]);
        }

        // Helper: Find existing item index in cart
        function findMatchingCartIndex(productId, variantId, addonIds, notes) {
            return cart.findIndex(item =>
                item.product_id === productId &&
                (item.variant_id || null) === (variantId || null) &&
                arraysMatch(item.addon_ids, addonIds) &&
                (item.notes || '') === (notes || '')
            );
        }

        // Fast lookup and add by product ID (Opens Customization Modal with Variants & Notes at once)
        function selectProductById(productId) {
            const product = allProductsCatalog[productId];
            if (product) selectProduct(product);
        }

        function quickAddProductById(productId) {
            const product = allProductsCatalog[productId];
            if (product) selectProduct(product);
        }

        let quickVariantProduct = null;

        function closeQuickVariantModal() {
            document.getElementById('quickVariantModal')?.classList.add('hidden');
            quickVariantProduct = null;
        }

        function openFullCustomizationFromQuick() {
            const p = quickVariantProduct;
            closeQuickVariantModal();
            if (p) selectProduct(p);
        }

        function addVariantDirectly(product, variant) {
            const unitPrice = parseFloat(product.base_price) + parseFloat(variant.price_modifier);
            const matchIdx = findMatchingCartIndex(product.id, variant.id, [], '');

            if (matchIdx > -1) {
                cart[matchIdx].quantity = (parseInt(cart[matchIdx].quantity) || 1) + 1;
            } else {
                cart.push({
                    product_id: product.id,
                    product_name: product.name,
                    variant_id: variant.id,
                    variant_name: variant.name,
                    addon_ids: [],
                    addons: [],
                    unit_price: unitPrice,
                    quantity: 1,
                    notes: '',
                });
            }

            closeQuickVariantModal();
            renderCart();
        }

        // Quick add from '+' button on catalog card
        function quickAddProduct(product) {
            let variantId = null;
            let variantName = null;
            let unitPrice = parseFloat(product.base_price);

            if (product.active_variants && product.active_variants.length > 0) {
                const def = product.active_variants[0];
                variantId = def.id;
                variantName = def.name;
                unitPrice += parseFloat(def.price_modifier);
            }

            const matchIdx = findMatchingCartIndex(product.id, variantId, [], '');
            if (matchIdx > -1) {
                cart[matchIdx].quantity = parseInt(cart[matchIdx].quantity) + 1;
            } else {
                cart.push({
                    product_id: product.id,
                    product_name: product.name,
                    variant_id: variantId,
                    variant_name: variantName,
                    addon_ids: [],
                    addons: [],
                    unit_price: unitPrice,
                    quantity: 1,
                    notes: '',
                });
            }
            renderCart();
        }

        // Product Selection (Opens customization modal with Variants & Custom Notes at once)
        function selectProduct(product) {
            currentModalProduct = product;
            selectedVariantId = null;
            selectedAddonIds = [];
            document.getElementById('modalItemNote').value = '';

            document.getElementById('modalProductName').innerText = product.name;
            document.getElementById('modalProductDescription').innerText = product.description || '';
            document.getElementById('modalProductCategory').innerText = product.category?.name || 'Beverage';

            const modalImg = document.getElementById('modalProductImage');
            modalImg.src = product.image_path || 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=600&q=80';

            // Populate Variants / Sizes
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
                        <label class="p-2.5 rounded-xl border-2 cursor-pointer flex items-center justify-between text-xs transition ${idx === 0 ? 'border-amber-500 bg-amber-50/50 text-amber-950 font-bold' : 'border-slate-200 hover:border-amber-400 bg-white text-slate-800 font-medium'}">
                            <div class="flex items-center">
                                <input type="radio" name="variant_choice" value="${v.id}" ${idx === 0 ? 'checked' : ''} onchange="updateVariantSelection(${v.id})" class="text-amber-500 focus:ring-amber-400 mr-2">
                                <span>${v.name}</span>
                            </div>
                            <span class="text-amber-600 font-bold text-[11px]">${modText}</span>
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
            document.querySelectorAll('#modalVariantsList label').forEach(lbl => {
                const input = lbl.querySelector('input');
                if (input && parseInt(input.value) === variantId) {
                    lbl.className = 'p-2.5 rounded-xl border-2 border-amber-500 bg-amber-50/50 text-amber-950 font-bold cursor-pointer flex items-center justify-between text-xs transition';
                } else {
                    lbl.className = 'p-2.5 rounded-xl border-2 border-slate-200 hover:border-amber-400 bg-white text-slate-800 font-medium cursor-pointer flex items-center justify-between text-xs transition';
                }
            });
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

            const note = document.getElementById('modalItemNote').value.trim();
            const matchIdx = findMatchingCartIndex(currentModalProduct.id, selectedVariantId, selectedAddonIds, note);

            if (matchIdx > -1) {
                // Already in cart with exact same options: add up quantity!
                cart[matchIdx].quantity = parseInt(cart[matchIdx].quantity) + 1;
            } else {
                // New item entry
                cart.push({
                    product_id: currentModalProduct.id,
                    product_name: currentModalProduct.name,
                    variant_id: selectedVariantId,
                    variant_name: variantName,
                    addon_ids: [...selectedAddonIds],
                    addons: chosenAddons,
                    unit_price: unitPrice,
                    quantity: 1,
                    notes: note,
                });
            }

            closeCustomizationModal();
            renderCart();
        }

        // Cart Rendering & Instant Local Calculations
        function renderCart() {
            const list = document.getElementById('cartItemsList');
            const emptyMsg = document.getElementById('emptyCartMessage');

            if (!list) return;

            if (cart.length === 0) {
                list.innerHTML = '';
                if (emptyMsg) emptyMsg.classList.remove('hidden');
                document.getElementById('btnCheckout').disabled = true;
                calculatedTotals = { subtotal: 0, discount_amount: 0, tax_amount: 0, total_amount: 0 };
                updateSummaryDisplay(0, 0, 0);
                return;
            }

            if (emptyMsg) emptyMsg.classList.add('hidden');
            list.innerHTML = '';

            let localSubtotal = 0;

            cart.forEach((item, index) => {
                const qty = parseInt(item.quantity) || 1;
                const itemTotal = item.unit_price * qty;
                localSubtotal += itemTotal;

                let addonsHtml = '';
                if (item.addons && item.addons.length > 0) {
                    addonsHtml = item.addons.map(a => `<span class="text-[10px] text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded mr-1 font-semibold">+${a.name} ($${parseFloat(a.price).toFixed(2)})</span>`).join('');
                }

                const itemRow = document.createElement('div');
                itemRow.className = 'p-3 rounded-2xl border border-slate-200 bg-white shadow-xs hover:border-amber-300 transition';
                itemRow.innerHTML = `
                    <div class="flex items-start justify-between mb-1.5">
                        <div class="pr-2">
                            <span class="font-bold text-xs text-slate-900 block leading-tight">${item.product_name}</span>
                            <div class="flex items-center space-x-1.5 mt-0.5">
                                ${item.variant_name ? `<span class="text-[11px] text-slate-600 font-semibold">${item.variant_name}</span>` : ''}
                                ${qty > 1 ? `<span class="text-[10px] text-slate-400 font-mono">($${parseFloat(item.unit_price).toFixed(2)} each)</span>` : ''}
                            </div>
                            ${item.notes ? `<span class="text-[10px] text-slate-400 italic block mt-0.5">Note: ${item.notes}</span>` : ''}
                        </div>
                        <span class="font-black text-slate-900 font-mono text-sm leading-tight">$${itemTotal.toFixed(2)}</span>
                    </div>

                    ${addonsHtml ? `<div class="mb-1.5 flex flex-wrap gap-1">${addonsHtml}</div>` : ''}

                    <div class="flex items-center justify-between pt-1.5 border-t border-slate-100 text-xs">
                        <div class="flex items-center space-x-1.5">
                            <button type="button" onclick="adjustQuantity(${index}, -1)" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-black flex items-center justify-center transition shadow-2xs">-</button>
                            <span class="w-8 text-center font-black text-slate-900 font-mono text-sm">${qty}</span>
                            <button type="button" onclick="adjustQuantity(${index}, 1)" class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-100 active:bg-amber-200 text-amber-700 font-black flex items-center justify-center transition shadow-2xs">+</button>
                        </div>
                        <button type="button" onclick="removeItem(${index})" class="text-[11px] font-bold text-rose-500 hover:text-rose-700 hover:underline">Remove</button>
                    </div>
                `;
                list.appendChild(itemRow);
            });

            localSubtotal = Math.round(localSubtotal * 100) / 100;
            calculatedTotals.subtotal = localSubtotal;
            const currentDiscount = calculatedTotals.discount_amount || 0;
            calculatedTotals.total_amount = Math.max(0, Math.round((localSubtotal - currentDiscount) * 100) / 100);

            // Synchronously update the display immediately so the cashier sees real-time numbers!
            updateSummaryDisplay(localSubtotal, currentDiscount, calculatedTotals.total_amount);

            document.getElementById('btnCheckout').disabled = false;
            fetchServerPricing();
        }

        function adjustQuantity(index, delta) {
            if (!cart[index]) return;
            cart[index].quantity = Math.max(0, (parseInt(cart[index].quantity) || 1) + delta);
            if (cart[index].quantity <= 0) {
                cart.splice(index, 1);
            }
            renderCart();
        }

        function removeItem(index) {
            if (!cart[index]) return;
            cart.splice(index, 1);
            renderCart();
        }

        function clearCart() {
            cart = [];
            renderCart();
        }

        // ================= CUSTOMER & LOYALTY PROGRAM SEARCH / SCAN =================
        function onCustomerSearchFocus() {
            const input = document.getElementById('customerSearchScanInput');
            onCustomerSearchInput(input ? input.value : '');
        }

        function onCustomerSearchInput(val) {
            const query = (val || '').toLowerCase().trim();
            const suggestions = document.getElementById('customerSuggestionsList');
            if (!suggestions) return;

            let matches = [];
            if (query.length === 0) {
                // Immediately suggest members upon click or focus
                matches = allCustomers.slice(0, 8);
            } else {
                matches = allCustomers.filter(c =>
                    c.name.toLowerCase().includes(query) ||
                    (c.phone && c.phone.includes(query)) ||
                    (c.email && c.email.toLowerCase().includes(query))
                );
            }

            if (matches.length === 0) {
                suggestions.innerHTML = `
                    <div class="p-3 text-center text-xs text-slate-400">
                        No customer found. <button type="button" onclick="openQuickNewCustomerModal('${val}')" class="text-amber-600 font-bold underline ml-1">+ Enroll "${val}"</button>
                    </div>
                `;
                suggestions.classList.remove('hidden');
                return;
            }

            suggestions.innerHTML = '';
            if (query.length === 0) {
                suggestions.innerHTML = `<div class="px-2.5 py-1 text-[9px] font-black uppercase text-amber-800 bg-amber-100/70 border-b border-amber-200">Loyalty Members (Click to select)</div>`;
            }

            matches.slice(0, 8).forEach(cust => {
                const item = document.createElement('div');
                item.className = 'p-2.5 hover:bg-amber-50 cursor-pointer border-b border-slate-100 flex items-center justify-between text-xs transition';
                item.onclick = (e) => {
                    e.stopPropagation();
                    attachCustomer(cust);
                };
                item.innerHTML = `
                    <div>
                        <span class="font-bold text-slate-900 block">${cust.name}</span>
                        <span class="text-[10px] text-slate-500 font-mono">${cust.phone || 'No phone'}</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800">
                        ${cust.points_balance} pts
                    </span>
                `;
                suggestions.appendChild(item);
            });
            suggestions.classList.remove('hidden');
        }

        // Close suggestions dropdown on outside click
        document.addEventListener('click', function(e) {
            const container = document.getElementById('customerSearchScanInput')?.parentElement;
            if (container && !container.contains(e.target)) {
                document.getElementById('customerSuggestionsList')?.classList.add('hidden');
            }
        });

        function handleCustomerScanKey(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const val = e.target.value.trim();
                const matched = allCustomers.find(c =>
                    c.phone === val || c.id == val || c.name.toLowerCase() === val.toLowerCase()
                );
                if (matched) {
                    attachCustomer(matched);
                } else if (val.length > 0) {
                    onCustomerSearchInput(val);
                }
            }
        }

        function triggerScanSimulation() {
            if (allCustomers.length > 0) {
                const sample = allCustomers[Math.floor(Math.random() * allCustomers.length)];
                document.getElementById('customerSearchScanInput').value = sample.phone;
                attachCustomer(sample);
            }
        }

        function attachCustomer(cust) {
            selectedCustomer = cust;
            document.getElementById('selectedCustomerId').value = cust.id;
            document.getElementById('customerSearchScanInput').value = '';
            document.getElementById('customerSuggestionsList').classList.add('hidden');

            document.getElementById('activeCustomerName').innerText = cust.name;
            document.getElementById('activeCustomerPhone').innerText = `Phone: ${cust.phone || 'N/A'}`;
            document.getElementById('activeCustomerPointsBadge').innerText = `${cust.points_balance} pts`;

            const historyLink = document.getElementById('activeCustomerHistoryLink');
            if (historyLink) {
                historyLink.href = `/customers/${cust.id}`;
            }

            // Setup Loyalty Reward Options for this customer
            const rewardSelect = document.getElementById('loyaltyRewardSelect');
            rewardSelect.value = '';
            document.getElementById('selectedLoyaltyRewardId').value = '';
            document.getElementById('loyaltyRewardNotice').classList.add('hidden');

            Array.from(rewardSelect.options).forEach(opt => {
                if (opt.value === '') return;
                const reqPts = parseInt(opt.dataset.points) || 0;
                if (cust.points_balance >= reqPts) {
                    opt.disabled = false;
                    opt.innerText = `${opt.dataset.desc} (Eligible - ${reqPts} pts)`;
                } else {
                    opt.disabled = true;
                    opt.innerText = `${opt.dataset.desc} (Need ${reqPts - cust.points_balance} more pts)`;
                }
            });

            document.getElementById('activeCustomerCard').classList.remove('hidden');
            fetchServerPricing();
        }

        function detachCustomer() {
            selectedCustomer = null;
            document.getElementById('selectedCustomerId').value = '';
            document.getElementById('selectedLoyaltyRewardId').value = '';
            document.getElementById('loyaltyRewardSelect').value = '';
            document.getElementById('activeCustomerCard').classList.add('hidden');
            document.getElementById('loyaltyRewardNotice').classList.add('hidden');
            fetchServerPricing();
        }

        function onLoyaltyRewardSelected() {
            const rewardId = document.getElementById('loyaltyRewardSelect').value;
            document.getElementById('selectedLoyaltyRewardId').value = rewardId;

            const notice = document.getElementById('loyaltyRewardNotice');
            if (rewardId) {
                const opt = document.getElementById('loyaltyRewardSelect').selectedOptions[0];
                notice.innerText = `Reward applied! Deducts ${opt.dataset.points} points upon checkout.`;
                notice.classList.remove('hidden');
            } else {
                notice.classList.add('hidden');
            }

            fetchServerPricing();
        }

        // ================= PROMOTIONS DURING CHECKOUT =================
        function applyPromoCodeManual() {
            const code = document.getElementById('promoCodeInput').value.trim().toUpperCase();
            if (!code) return;

            const promo = allPromotions.find(p => p.code && p.code.toUpperCase() === code);
            if (promo) {
                selectPromoDirect(promo.id, promo.code);
            } else {
                alert(`Promo code '${code}' is invalid or expired.`);
            }
        }

        function selectPromoDirect(promoId, promoCode) {
            const promo = allPromotions.find(p => p.id === promoId);
            if (!promo) return;

            document.getElementById('selectedPromotionId').value = promo.id;
            document.getElementById('promoCodeInput').value = promo.code;
            document.getElementById('appliedPromoName').innerText = `${promo.name} (${promo.type === 'percentage' ? promo.value+'%' : '$'+promo.value} off)`;
            document.getElementById('appliedPromoBadge').classList.remove('hidden');

            fetchServerPricing();
        }

        function removeAppliedPromo() {
            document.getElementById('selectedPromotionId').value = '';
            document.getElementById('promoCodeInput').value = '';
            document.getElementById('appliedPromoBadge').classList.add('hidden');
            fetchServerPricing();
        }

        function togglePromoList() {
            const container = document.getElementById('promoChipsContainer');
            container.classList.toggle('hidden');
        }

        // ================= SERVER PRICING CALCULATION (TAX REMOVED) =================
        async function fetchServerPricing() {
            if (cart.length === 0) return;

            const promoId = document.getElementById('selectedPromotionId').value;
            const customerId = document.getElementById('selectedCustomerId').value;
            const loyaltyRewardId = document.getElementById('selectedLoyaltyRewardId').value;

            const payloadItems = cart.map(it => ({
                product_id: it.product_id,
                variant_id: it.variant_id || null,
                addon_ids: it.addon_ids || [],
                quantity: it.quantity,
                notes: it.notes || null
            }));

            try {
                const response = await fetch("{{ route('pos.calculate') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        items: payloadItems,
                        promotion_id: promoId ? parseInt(promoId) : null,
                        customer_id: customerId ? parseInt(customerId) : null,
                        loyalty_reward_id: loyaltyRewardId ? parseInt(loyaltyRewardId) : null,
                    })
                });

                const data = await response.json();
                if (data.success) {
                    calculatedTotals = data.data;
                    updateSummaryDisplay(
                        calculatedTotals.subtotal,
                        calculatedTotals.discount_amount,
                        calculatedTotals.total_amount
                    );
                }
            } catch (err) {
                console.error("Server pricing calculation error:", err);
            }
        }

        function updateSummaryDisplay(subtotal, discount, total) {
            const subEl = document.getElementById('cartSubtotal');
            if (subEl) subEl.innerText = '$' + subtotal.toFixed(2);

            const discountRow = document.getElementById('discountRow');
            const discEl = document.getElementById('cartDiscount');
            if (discount > 0) {
                if (discountRow) discountRow.classList.remove('hidden');
                if (discEl) discEl.innerText = '-$' + discount.toFixed(2);
            } else {
                if (discountRow) discountRow.classList.add('hidden');
            }

            // TAX is removed completely
            const totalEl = document.getElementById('cartTotal');
            if (totalEl) totalEl.innerText = '$' + total.toFixed(2);
            const btnCheckoutTotal = document.getElementById('btnCheckoutTotal');
            if (btnCheckoutTotal) btnCheckoutTotal.innerText = '$' + total.toFixed(2);

            // Keep floating collapsed indicator synced
            const floatTotal = document.getElementById('floatingCartTotal');
            if (floatTotal) floatTotal.innerText = '$' + total.toFixed(2);
            let totalItems = 0;
            cart.forEach(it => { totalItems += (parseInt(it.quantity) || 1); });
            const floatCount = document.getElementById('floatingCartCount');
            if (floatCount) floatCount.innerText = totalItems;
        }

        // Helper to safely update submit order button state without destroying child DOM elements
        function updateSubmitOrderButton(loading, totalAmount = null) {
            const btn = document.getElementById('btnSubmitOrder');
            if (!btn) return;
            btn.disabled = !!loading;

            let textSpan = document.getElementById('btnSubmitOrderText');
            let totalSpan = document.getElementById('btnSubmitOrderTotal');

            if (!textSpan || !totalSpan) {
                btn.innerHTML = `<span id="btnSubmitOrderText">Confirm & Complete Order</span> <span id="btnSubmitOrderTotal" class="font-mono">$0.00</span>`;
                textSpan = document.getElementById('btnSubmitOrderText');
                totalSpan = document.getElementById('btnSubmitOrderTotal');
            }

            if (loading) {
                if (textSpan) textSpan.innerText = 'Processing Payment...';
                if (totalSpan) totalSpan.innerText = '';
            } else {
                if (textSpan) textSpan.innerText = 'Confirm & Complete Order';
                if (totalSpan && totalAmount !== null) {
                    const amt = typeof totalAmount === 'number' ? totalAmount : parseFloat(totalAmount || 0);
                    totalSpan.innerText = '$' + (isNaN(amt) ? '0.00' : amt.toFixed(2));
                }
            }
        }

        // ================= REALISTIC PAYMENT MODAL WORKFLOW =================
        function openPaymentModal() {
            if (cart.length === 0) return;

            let total = calculatedTotals.total_amount;
            if (!total || total <= 0) {
                let sum = 0;
                cart.forEach(it => { sum += (it.unit_price * (parseInt(it.quantity) || 1)); });
                const disc = calculatedTotals.discount_amount || 0;
                total = Math.max(0, Math.round((sum - disc) * 100) / 100);
                calculatedTotals.total_amount = total;
            }

            const dueEl = document.getElementById('payModalAmountDue');
            if (dueEl) dueEl.innerText = '$' + total.toFixed(2);

            const qrEl = document.getElementById('qrAmountDisplay');
            if (qrEl) qrEl.innerText = '$' + total.toFixed(2);

            // Cash tendered starts blank per cashier requirements
            const cashInput = document.getElementById('cashReceivedInput');
            if (cashInput) cashInput.value = '';

            const changeDisplay = document.getElementById('cashChangeDisplay');
            if (changeDisplay) changeDisplay.innerText = '$0.00';
            cashInputFresh = true;

            // Generate fresh random card authorization ref
            const cardRefEl = document.getElementById('cardAuthRef');
            if (cardRefEl) cardRefEl.value = 'CARD-AUTH-' + Math.floor(100000 + Math.random() * 900000);

            // Safely re-enable and update submit button state with current total
            updateSubmitOrderButton(false, total);

            // Customer info on payment modal
            const custNameEl = document.getElementById('payModalCustomerName');
            if (custNameEl) {
                if (selectedCustomer) {
                    custNameEl.innerText = `${selectedCustomer.name} (${selectedCustomer.points_balance} pts)`;
                } else {
                    custNameEl.innerText = 'Walk-in Customer';
                }
            }

            calculateCashChange();
            const payTabs = document.querySelectorAll('.pay-method-tab');
            if (payTabs.length > 0) {
                selectPaymentMethod('cash', payTabs[0]);
            }
            document.getElementById('paymentModal')?.classList.remove('hidden');
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }

        function selectPaymentMethod(method, btn) {
            currentPaymentMethod = method;
            document.querySelectorAll('.pay-method-tab').forEach(b => {
                b.classList.remove('active', 'bg-amber-500', 'text-white', 'shadow-md');
                b.classList.add('bg-slate-100', 'text-slate-700');
            });
            if (btn) {
                btn.classList.add('active', 'bg-amber-500', 'text-white', 'shadow-md');
                btn.classList.remove('bg-slate-100', 'text-slate-700');
            }

            const cashSec = document.getElementById('payCashSection');
            const cardSec = document.getElementById('payCardSection');
            const qrSec = document.getElementById('payQrSection');

            cashSec?.classList.add('hidden');
            cardSec?.classList.add('hidden');
            qrSec?.classList.add('hidden');

            if (method === 'cash') {
                cashSec?.classList.remove('hidden');
            } else if (method === 'card') {
                cardSec?.classList.remove('hidden');
            } else if (method === 'qr_code') {
                qrSec?.classList.remove('hidden');
            }
        }

        function quickTender(amount) {
            cashInputFresh = false;
            if (amount === 'exact') {
                document.getElementById('cashReceivedInput').value = calculatedTotals.total_amount.toFixed(2);
            } else {
                document.getElementById('cashReceivedInput').value = parseFloat(amount).toFixed(2);
            }
            calculateCashChange();
        }

        function numpadInput(val) {
            const input = document.getElementById('cashReceivedInput');
            let current = input.value;

            if (val === 'clear') {
                input.value = '';
                cashInputFresh = false;
            } else if (val === 'backspace') {
                input.value = current.slice(0, -1);
                cashInputFresh = false;
            } else if (val === '.') {
                if (cashInputFresh || !current) {
                    input.value = '0.';
                } else if (!current.includes('.')) {
                    input.value = current + '.';
                }
                cashInputFresh = false;
            } else {
                if (cashInputFresh || current === '0') {
                    input.value = val;
                } else {
                    input.value = current + val;
                }
                cashInputFresh = false;
            }
            calculateCashChange();
        }

        function calculateCashChange() {
            const raw = document.getElementById('cashReceivedInput').value.trim();
            if (raw === '') {
                document.getElementById('cashChangeDisplay').innerText = '$0.00';
                return;
            }
            const received = parseFloat(raw) || 0;
            const due = calculatedTotals.total_amount;
            const change = Math.max(0, received - due);
            document.getElementById('cashChangeDisplay').innerText = '$' + change.toFixed(2);
        }

        // ================= SUBMIT CHECKOUT & SHOW REALISTIC THERMAL RECEIPT =================
        async function submitOrderCheckout() {
            let totalToPay = calculatedTotals.total_amount;
            if (!totalToPay || totalToPay <= 0) {
                let sum = 0;
                cart.forEach(it => { sum += (it.unit_price * (parseInt(it.quantity) || 1)); });
                totalToPay = Math.round(sum * 100) / 100;
            }

            updateSubmitOrderButton(true);

            const cashInputRaw = (document.getElementById('cashReceivedInput')?.value || '').trim();
            const cashReceived = cashInputRaw !== '' ? (parseFloat(cashInputRaw) || totalToPay) : totalToPay;
            const cardRef = document.getElementById('cardAuthRef')?.value || `AUTH-${Date.now()}`;
            const digitalRef = currentPaymentMethod === 'cash' ? null : (currentPaymentMethod === 'card' ? cardRef : `QR-${Date.now()}`);

            const payloadItems = cart.map(it => ({
                product_id: it.product_id,
                variant_id: it.variant_id || null,
                addon_ids: it.addon_ids || [],
                quantity: it.quantity,
                notes: it.notes || null
            }));

            const payload = {
                order_type: activeOrderType,
                table_number: (document.getElementById('orderTableNumber')?.value || '').trim() || null,
                customer_id: document.getElementById('selectedCustomerId')?.value ? parseInt(document.getElementById('selectedCustomerId').value) : null,
                promotion_id: document.getElementById('selectedPromotionId')?.value ? parseInt(document.getElementById('selectedPromotionId').value) : null,
                loyalty_reward_id: document.getElementById('selectedLoyaltyRewardId')?.value ? parseInt(document.getElementById('selectedLoyaltyRewardId').value) : null,
                items: payloadItems,
                payments: [{
                    method: currentPaymentMethod,
                    amount: totalToPay,
                    cash_received: currentPaymentMethod === 'cash' ? cashReceived : null,
                    reference: currentPaymentMethod === 'cash' ? null : digitalRef,
                }]
            };

            try {
                const response = await fetch("{{ route('pos.checkout') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                if (result.success) {
                    closePaymentModal();

                    // Immediately clear old order products, customer, promo, table number, and money inputs
                    clearCart();
                    detachCustomer();
                    removeAppliedPromo();
                    const tableInput = document.getElementById('orderTableNumber');
                    if (tableInput) tableInput.value = '';
                    const cashInput = document.getElementById('cashReceivedInput');
                    if (cashInput) cashInput.value = '';
                    const changeDisplay = document.getElementById('cashChangeDisplay');
                    if (changeDisplay) changeDisplay.innerText = '$0.00';
                    const cardRefEl = document.getElementById('cardAuthRef');
                    if (cardRefEl) cardRefEl.value = 'CARD-AUTH-' + Math.floor(100000 + Math.random() * 900000);

                    // Sync updated customer points balance in local memory
                    if (result.receipt_data && result.receipt_data.customer) {
                        const cust = allCustomers.find(c => c.id === result.receipt_data.customer.id);
                        if (cust) {
                            cust.points_balance = result.receipt_data.customer.points_balance;
                        }
                    }

                    if (result.receipt_data) {
                        displayReceiptModal(result.receipt_data, result.receipt_url);
                    } else {
                        fetch("{{ url('/pos/orders') }}/" + result.order.id + "/receipt-data", {
                            headers: { 'Accept': 'application/json' }
                        })
                            .then(r => r.json())
                            .then(data => displayReceiptModal(data, result.receipt_url));
                    }
                } else {
                    if (result.requires_shift) {
                        closePaymentModal();
                        openStartShiftModal();
                        alert("Shift required: Please select station and start cashier shift.");
                        return;
                    }
                    alert("Checkout Error: " + (result.message || "Could not process order."));
                }
            } catch (err) {
                alert("Network or Server error during checkout.");
                console.error(err);
            } finally {
                updateSubmitOrderButton(false, calculatedTotals.total_amount || 0);
            }
        }

        function displayReceiptModal(data, printUrl) {
            document.getElementById('receiptStoreName').innerText = data.cafe_name;
            document.getElementById('receiptStoreAddress').innerText = data.cafe_address;
            document.getElementById('receiptStorePhone').innerText = 'Tel: ' + data.cafe_phone;
            document.getElementById('receiptOrderNumber').innerText = '#' + data.order_number;
            document.getElementById('receiptDateTime').innerText = data.date;
            document.getElementById('receiptCashier').innerText = data.cashier;
            document.getElementById('receiptOrderType').innerText = data.order_type + (data.table_number ? ' (' + data.table_number + ')' : '');

            // Customer Loyalty Section on Receipt
            const custSec = document.getElementById('receiptCustomerSection');
            if (data.customer) {
                custSec.classList.remove('hidden');
                document.getElementById('receiptCustomerName').innerText = data.customer.name;
                document.getElementById('receiptPointsEarned').innerText = '+' + data.customer.points_earned + ' pts';
                document.getElementById('receiptPointsBalance').innerText = data.customer.points_balance + ' pts';
            } else {
                custSec.classList.add('hidden');
            }

            // Items list
            const itemsContainer = document.getElementById('receiptItemsContainer');
            itemsContainer.innerHTML = '';
            data.items.forEach(it => {
                let addons = '';
                if (it.addons && it.addons.length > 0) {
                    addons = it.addons.map(a => `<div class="text-[9px] text-slate-500 pl-2">+ ${a.name}</div>`).join('');
                }
                const row = document.createElement('div');
                row.className = 'py-0.5';
                row.innerHTML = `
                    <div class="flex justify-between font-bold">
                        <span class="flex-2">${it.name} ${it.variant ? '('+it.variant+')' : ''}</span>
                        <span class="flex-1 text-center font-mono">x${it.quantity}</span>
                        <span class="flex-1 text-right font-mono">$${it.subtotal.toFixed(2)}</span>
                    </div>
                    ${addons}
                `;
                itemsContainer.appendChild(row);
            });

            // Financials (TAX REMOVED)
            document.getElementById('receiptSubtotal').innerText = '$' + data.subtotal.toFixed(2);
            const discRow = document.getElementById('receiptDiscountRow');
            if (data.discount_amount > 0) {
                discRow.classList.remove('hidden');
                document.getElementById('receiptDiscount').innerText = '-$' + data.discount_amount.toFixed(2);
            } else {
                discRow.classList.add('hidden');
            }
            document.getElementById('receiptTotal').innerText = '$' + data.total_amount.toFixed(2);

            // Payments
            const paySec = document.getElementById('receiptPaymentDetails');
            paySec.innerHTML = '';
            data.payments.forEach(p => {
                let extra = '';
                if (p.cash_received) {
                    extra = `
                        <div class="flex justify-between"><span>Cash Tendered:</span><span class="font-mono">$${p.cash_received.toFixed(2)}</span></div>
                        <div class="flex justify-between font-bold text-slate-900"><span>Change Given:</span><span class="font-mono">$${(p.cash_change || 0).toFixed(2)}</span></div>
                    `;
                }
                if (p.reference) {
                    extra += `<div class="flex justify-between"><span>Ref:</span><span class="font-mono">${p.reference}</span></div>`;
                }
                paySec.innerHTML += `
                    <div class="flex justify-between font-bold"><span>Payment (${p.method}):</span><span class="font-mono">$${p.amount.toFixed(2)}</span></div>
                    ${extra}
                `;
            });

            document.getElementById('receiptBarcodeText').innerText = data.order_number;
            document.getElementById('receiptStoreFooter').innerText = data.receipt_footer;
            document.getElementById('receiptPrintLink').href = printUrl;

            document.getElementById('receiptModal').classList.remove('hidden');
        }

        function resetPosForNextOrder() {
            document.getElementById('receiptModal')?.classList.add('hidden');
            document.getElementById('paymentModal')?.classList.add('hidden');
            document.getElementById('customizationModal')?.classList.add('hidden');
            clearCart();
            detachCustomer();
            removeAppliedPromo();
            const tableInput = document.getElementById('orderTableNumber');
            if (tableInput) tableInput.value = '';
            const cashInput = document.getElementById('cashReceivedInput');
            if (cashInput) cashInput.value = '';
            const changeDisplay = document.getElementById('cashChangeDisplay');
            if (changeDisplay) changeDisplay.innerText = '$0.00';
            const cardRefEl = document.getElementById('cardAuthRef');
            if (cardRefEl) cardRefEl.value = 'CARD-AUTH-' + Math.floor(100000 + Math.random() * 900000);
            calculatedTotals = { subtotal: 0, discount_amount: 0, tax_amount: 0, total_amount: 0 };
            updateSummaryDisplay(0, 0, 0);
            updateSubmitOrderButton(false, 0);
        }

        // ================= SIDEBAR RESIZING & COLLAPSE =================
        function setSidebarWidth(mode) {
            const sb = document.getElementById('posSidebar');
            if (!sb) return;
            sb.classList.remove('w-[340px]', 'w-[420px]', 'w-[520px]');
            document.querySelectorAll('.sidebar-size-btn').forEach(b => {
                b.classList.remove('active', 'bg-white', 'text-slate-900', 'shadow-2xs', 'font-extrabold');
                b.classList.add('text-slate-600');
            });
            if (mode === 'compact') {
                sb.classList.add('w-[340px]');
                document.querySelectorAll('.sidebar-size-btn')[0]?.classList.add('active', 'bg-white', 'text-slate-900', 'shadow-2xs', 'font-extrabold');
            } else if (mode === 'wide') {
                sb.classList.add('w-[520px]');
                document.querySelectorAll('.sidebar-size-btn')[2]?.classList.add('active', 'bg-white', 'text-slate-900', 'shadow-2xs', 'font-extrabold');
            } else {
                sb.classList.add('w-[420px]');
                document.querySelectorAll('.sidebar-size-btn')[1]?.classList.add('active', 'bg-white', 'text-slate-900', 'shadow-2xs', 'font-extrabold');
            }
        }

        function toggleSidebarCollapse() {
            const sb = document.getElementById('posSidebar');
            const floatBtn = document.getElementById('floatingOpenSidebarBtn');
            if (!sb) return;
            if (sb.classList.contains('hidden')) {
                sb.classList.remove('hidden');
                floatBtn?.classList.add('hidden');
            } else {
                sb.classList.add('hidden');
                floatBtn?.classList.remove('hidden');
            }
        }

        // ================= SHIFT MODAL CONTROLS =================
        function openStartShiftModal() {
            document.getElementById('startShiftModal')?.classList.remove('hidden');
        }

        function closeStartShiftModal() {
            document.getElementById('startShiftModal')?.classList.add('hidden');
        }

        function openEndShiftModal() {
            document.getElementById('endShiftModal')?.classList.remove('hidden');
        }

        function closeEndShiftModal() {
            document.getElementById('endShiftModal')?.classList.add('hidden');
        }

        // ================= QUICK NEW CUSTOMER ENROLLMENT =================
        function openQuickNewCustomerModal(initialVal = '') {
            if (initialVal) {
                if (/^\d+$/.test(initialVal)) {
                    document.getElementById('quickCustomerPhone').value = initialVal;
                    document.getElementById('quickCustomerName').value = '';
                } else {
                    document.getElementById('quickCustomerName').value = initialVal;
                    document.getElementById('quickCustomerPhone').value = '';
                }
            } else {
                document.getElementById('quickCustomerName').value = '';
                document.getElementById('quickCustomerPhone').value = '';
            }
            document.getElementById('quickCustomerEmail').value = '';
            document.getElementById('customerSuggestionsList').classList.add('hidden');
            document.getElementById('quickCustomerModal').classList.remove('hidden');
        }

        function closeQuickNewCustomerModal() {
            document.getElementById('quickCustomerModal').classList.add('hidden');
        }

        async function submitQuickCustomerEnrollment() {
            const name = document.getElementById('quickCustomerName').value.trim();
            const phone = document.getElementById('quickCustomerPhone').value.trim();
            const email = document.getElementById('quickCustomerEmail').value.trim();

            if (!name || !phone) {
                alert("Please enter customer name and phone number.");
                return;
            }

            try {
                const res = await fetch("{{ route('pos.customers.quick_create') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ name, phone, email: email || null })
                });

                const data = await res.json();
                if (data.success) {
                    allCustomers.push(data.customer);
                    attachCustomer(data.customer);
                    closeQuickNewCustomerModal();
                } else {
                    alert("Error: " + (data.message || "Could not register customer."));
                }
            } catch (e) {
                alert("Server error enrolling customer.");
                console.error(e);
            }
        }

        @if(!$activeShift)
        document.addEventListener('DOMContentLoaded', function () {
            openStartShiftModal();
        });
        @endif
    </script>
</body>
</html>
