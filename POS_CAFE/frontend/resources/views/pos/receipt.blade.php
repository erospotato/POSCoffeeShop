<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $order->order_number }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 15px;
            width: 78mm;
            box-sizing: border-box;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .border-top { border-top: 1px dashed #000; }
        .border-bottom { border-bottom: 1px dashed #000; }
        .my-2 { margin-top: 6px; margin-bottom: 6px; }
        .py-1 { padding-top: 3px; padding-bottom: 3px; }
        .flex { display: flex; justify-content: space-between; }
        .print-btn {
            display: block;
            width: 100%;
            padding: 8px;
            background: #000;
            color: #fff;
            text-align: center;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 15px;
            border: none;
            border-radius: 4px;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 5px; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Print Receipt</button>
    </div>

    <div class="text-center">
        <img src="{{ asset('images/logo.svg') }}" alt="Veloura Café" style="height: 44px; margin: 0 auto 6px auto; display: block;">
        <h2 style="margin: 0; font-size: 16px;">{{ $cafeName }}</h2>
        <p style="margin: 3px 0;">{{ $cafeAddress }}</p>
        <p style="margin: 2px 0;">Tel: {{ $cafePhone }}</p>
    </div>

    <div class="border-bottom my-2"></div>

    <div class="flex">
        <span>Order:</span>
        <span class="font-bold">{{ $order->order_number }}</span>
    </div>
    <div class="flex">
        <span>Date:</span>
        <span>{{ $order->created_at->format('d/m/Y H:i:s') }}</span>
    </div>
    <div class="flex">
        <span>Cashier:</span>
        <span>{{ $order->cashier->name ?? 'Staff' }}</span>
    </div>
    @if($order->customer)
        <div class="flex">
            <span>Customer:</span>
            <span>{{ $order->customer->name }}</span>
        </div>
        <div class="flex">
            <span>Points Earned:</span>
            <span>+{{ floor($order->total_amount) }} pts</span>
        </div>
    @endif
    <div class="flex">
        <span>Type:</span>
        <span class="font-bold">{{ strtoupper(str_replace('_', ' ', $order->order_type)) }} {{ $order->table_number ? '('.$order->table_number.')' : '' }}</span>
    </div>

    <div class="border-top border-bottom my-2 py-1 font-bold flex">
        <span style="flex: 2;">Item</span>
        <span style="flex: 1; text-align: center;">Qty</span>
        <span style="flex: 1; text-align: right;">Total</span>
    </div>

    @foreach($order->items as $item)
        <div class="py-1">
            <div class="flex">
                <span style="flex: 2;" class="font-bold">{{ $item->item_name }}</span>
                <span style="flex: 1; text-align: center;">{{ $item->quantity }}</span>
                <span style="flex: 1; text-align: right;">${{ number_format($item->subtotal, 2) }}</span>
            </div>
            @if($item->variant_name)
                <div style="font-size: 10px; color: #333; margin-left: 8px;">• {{ $item->variant_name }}</div>
            @endif
            @foreach($item->addons as $addon)
                <div style="font-size: 10px; color: #333; margin-left: 8px;">+ {{ $addon->addon_name }} (${{ number_format($addon->price, 2) }})</div>
            @endforeach
            @if($item->preparation_notes)
                <div style="font-size: 10px; font-style: italic; margin-left: 8px;">Note: {{ $item->preparation_notes }}</div>
            @endif
        </div>
    @endforeach

    <div class="border-top my-2"></div>

    <div class="flex">
        <span>Subtotal:</span>
        <span>${{ number_format($order->subtotal, 2) }}</span>
    </div>
    @if((float) $order->discount_amount > 0)
        <div class="flex">
            <span>Discount / Loyalty:</span>
            <span>-${{ number_format($order->discount_amount, 2) }}</span>
        </div>
    @endif

    <div class="border-top my-2"></div>

    <div class="flex" style="font-size: 15px;">
        <span class="font-bold">TOTAL:</span>
        <span class="font-bold">${{ number_format($order->total_amount, 2) }}</span>
    </div>

    <div class="border-bottom my-2"></div>

    @foreach($order->payments as $payment)
        <div class="flex">
            <span>Payment ({{ ucfirst($payment->payment_method) }}):</span>
            <span>${{ number_format($payment->amount, 2) }}</span>
        </div>
        @if($payment->payment_method === 'cash' && $payment->cash_received)
            <div class="flex">
                <span>Cash Tendered:</span>
                <span>${{ number_format($payment->cash_received, 2) }}</span>
            </div>
            <div class="flex font-bold">
                <span>Change Given:</span>
                <span>${{ number_format($payment->cash_change, 2) }}</span>
            </div>
        @endif
        @if($payment->transaction_reference)
            <div class="flex" style="font-size: 10px; color: #444;">
                <span>Ref:</span>
                <span>{{ $payment->transaction_reference }}</span>
            </div>
        @endif
    @endforeach

    <div class="border-bottom my-2"></div>

    <div class="text-center my-2" style="font-size: 11px;">
        <p>{{ $receiptFooter }}</p>
        <p style="margin-top: 8px; font-size: 9px; color: #666;">Generated by Veloura Café POS</p>
    </div>
</body>
</html>
