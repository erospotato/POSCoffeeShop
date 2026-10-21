<x-app-layout>
    <x-slot name="header">
        System Settings & Configuration
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- General Café Brand & Profile -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="border-b border-slate-100 pb-3 mb-5">
                    <h2 class="text-sm font-bold text-slate-900">Café Store Profile</h2>
                    <p class="text-xs text-slate-500">Business identity printed on receipts and invoices.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Café Brand Name</label>
                        <input type="text" name="settings[cafe_name]"
                               value="{{ old('settings.cafe_name', $settings->get('cafe_name')?->value ?? 'Veloura Café') }}"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Contact Phone</label>
                        <input type="text" name="settings[cafe_phone]"
                               value="{{ old('settings.cafe_phone', $settings->get('cafe_phone')?->value ?? '+855 23 888 999') }}"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Store Address</label>
                        <input type="text" name="settings[cafe_address]"
                               value="{{ old('settings.cafe_address', $settings->get('cafe_address')?->value ?? 'No. 88 Heritage Blvd') }}"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Contact Email</label>
                        <input type="email" name="settings[cafe_email]"
                               value="{{ old('settings.cafe_email', $settings->get('cafe_email')?->value ?? 'hello@velouracafe.com') }}"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>
            </div>

            <!-- Tax & Pricing Configuration -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="border-b border-slate-100 pb-3 mb-5">
                    <h2 class="text-sm font-bold text-slate-900">Tax & Pricing Parameters</h2>
                    <p class="text-xs text-slate-500">Define VAT/sales tax percentages and pricing mode (tax inclusive or exclusive).</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Currency Symbol</label>
                        <input type="text" name="settings[currency_symbol]"
                               value="{{ old('settings.currency_symbol', $settings->get('currency_symbol')?->value ?? '$') }}"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tax Rate (%)</label>
                        <input type="number" step="0.1" name="settings[tax_rate]"
                               value="{{ old('settings.tax_rate', $settings->get('tax_rate')?->value ?? '0.0') }}"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 font-bold">
                        <span class="text-[10px] text-slate-400 mt-1 block">Default set to 0.0% (No Tax)</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tax Calculation Mode</label>
                        @php $mode = old('settings.tax_mode', $settings->get('tax_mode')?->value ?? 'exclusive'); @endphp
                        <select name="settings[tax_mode]" class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                            <option value="exclusive" {{ $mode === 'exclusive' ? 'selected' : '' }}>Exclusive (Added on top)</option>
                            <option value="inclusive" {{ $mode === 'inclusive' ? 'selected' : '' }}>Inclusive (Embedded in price)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- POS & Receipt Layout -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="border-b border-slate-100 pb-3 mb-5">
                    <h2 class="text-sm font-bold text-slate-900">POS & Thermal Receipt Configuration</h2>
                    <p class="text-xs text-slate-500">Customize ticket prefixes and thermal printer receipt messages.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Order Prefix</label>
                        <input type="text" name="settings[order_prefix]"
                               value="{{ old('settings.order_prefix', $settings->get('order_prefix')?->value ?? 'ORD') }}"
                               class="w-48 text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 font-mono uppercase">
                        <p class="text-[11px] text-slate-400 mt-1">Generates numbers such as ORD-20261020-001</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Receipt Footer Note</label>
                        <textarea name="settings[receipt_footer]" rows="2"
                                  class="w-full text-xs px-3.5 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">{{ old('settings.receipt_footer', $settings->get('receipt_footer')?->value) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Loyalty & Inventory Rules -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="border-b border-slate-100 pb-3 mb-5">
                    <h2 class="text-sm font-bold text-slate-900">Operational Rules</h2>
                    <p class="text-xs text-slate-500">Configure loyalty conversion rates and inventory bounds.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Loyalty Points Per $1 Spent</label>
                        <input type="number" step="0.1" name="settings[loyalty_points_per_dollar]"
                               value="{{ old('settings.loyalty_points_per_dollar', $settings->get('loyalty_points_per_dollar')?->value ?? '1.0') }}"
                               class="w-full text-xs px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Inventory Negative Stocks</label>
                        @php $allowNeg = old('settings.allow_negative_inventory', $settings->get('allow_negative_inventory')?->value ?? '1'); @endphp
                        <select name="settings[allow_negative_inventory]" class="w-full text-xs px-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                            <option value="1" {{ $allowNeg == '1' ? 'selected' : '' }}>Allow Negative Stock Override (Recommended for uninterrupted POS sales)</option>
                            <option value="0" {{ $allowNeg == '0' ? 'selected' : '' }}>Strict (Prevent Negative Stock)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    Save System Settings
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
