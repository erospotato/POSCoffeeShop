<?php

namespace App\Http\Controllers\Pos;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\CheckoutRequest;
use App\Http\Requests\Pos\RefundRequest;
use App\Models\Category;
use App\Models\Customer;
use App\Models\LoyaltyReward;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\SystemSetting;
use App\Services\PaymentService;
use App\Services\PosService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PosController extends Controller
{
    public function __construct(
        protected PosService $posService,
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $activeShift = $user->activeShift();

        $categories = Category::with(['products' => function ($q) {
            $q->where('is_available', true)->with(['activeVariants', 'activeAddons']);
        }])->where('is_active', true)->orderBy('sort_order')->get();

        $allProducts = Product::with(['category', 'activeVariants', 'activeAddons'])
            ->where('is_available', true)
            ->get();

        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        $promotions = Promotion::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString());
            })
            ->get();

        $loyaltyRewards = LoyaltyReward::where('is_active', true)->orderBy('points_required')->get();

        $taxRate = 0.0;
        $taxMode = 'none';
        $currency = SystemSetting::get('currency_symbol', '$');

        return view('pos.index', compact(
            'categories',
            'allProducts',
            'customers',
            'promotions',
            'loyaltyRewards',
            'activeShift',
            'taxRate',
            'taxMode',
            'currency'
        ));
    }

    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'loyalty_reward_id' => ['nullable', 'exists:loyalty_rewards,id'],
        ]);

        try {
            $pricing = $this->posService->calculatePricing(
                $request->input('items'),
                $request->input('promotion_id'),
                $request->input('customer_id'),
                $request->input('loyalty_reward_id')
            );

            return response()->json([
                'success' => true,
                'data' => $pricing,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $user = $request->user();

        // Ensure Cashier has open shift
        if ($user->isCashier() && !$user->activeShift()) {
            return response()->json([
                'success' => false,
                'message' => 'Please open a shift before completing transactions.',
                'requires_shift' => true,
            ], 422);
        }

        try {
            $order = $this->posService->createOrder($user, $request->validated());

            $paymentResult = $this->paymentService->processPayments(
                $order,
                $request->input('payments'),
                $user
            );

            $order->load(['items.addons', 'payments', 'cashier', 'customer']);

            return response()->json([
                'success' => true,
                'message' => 'Order created and payment processed successfully.',
                'order' => $order,
                'is_fully_paid' => $paymentResult['is_fully_paid'],
                'receipt_url' => route('pos.receipt', $order->id),
                'receipt_data' => $this->formatReceiptData($order),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function receipt(Order $order)
    {
        $order->load(['items.addons', 'payments', 'cashier', 'customer']);

        $cafeName = SystemSetting::get('cafe_name', 'Artisan Roastery & Café');
        $cafeAddress = SystemSetting::get('cafe_address', 'No. 88 Heritage Blvd');
        $cafePhone = SystemSetting::get('cafe_phone', '+855 23 888 999');
        $receiptFooter = SystemSetting::get('receipt_footer', 'Thank you for your visit!');
        $currency = SystemSetting::get('currency_symbol', '$');

        return view('pos.receipt', compact('order', 'cafeName', 'cafeAddress', 'cafePhone', 'receiptFooter', 'currency'));
    }

    public function cancel(Request $request, Order $order)
    {
        $request->validate(['reason' => ['required', 'string', 'min:3', 'max:500']]);

        if (!Gate::allows('cancel', $order)) {
            abort(403, 'Unauthorized to cancel this order.');
        }

        $order->status = OrderStatus::CANCELLED;
        $order->cancelled_at = now();
        $order->cancellation_reason = $request->input('reason');
        $order->cancelled_by = $request->user()->id;
        $order->save();

        return back()->with('success', "Order #{$order->order_number} has been cancelled.");
    }

    public function refund(RefundRequest $request, Order $order)
    {
        if (!Gate::allows('refund', $order)) {
            abort(403, 'Unauthorized to issue refunds.');
        }

        try {
            $refund = $this->paymentService->processRefund(
                $order,
                (float) $request->input('amount'),
                $request->input('reason'),
                $request->user(),
                (bool) $request->input('restock_inventory', false)
            );

            return back()->with('success', "Refund of $" . number_format($refund->amount, 2) . " processed successfully.");
        } catch (Exception $e) {
            return back()->withErrors(['refund_error' => $e->getMessage()]);
        }
    }

    public function formatReceiptData(Order $order): array
    {
        $order->loadMissing(['items.addons', 'payments', 'cashier', 'customer']);

        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'date' => $order->created_at->format('d/m/Y H:i:s'),
            'cashier' => $order->cashier?->name ?? 'Staff',
            'order_type' => strtoupper(str_replace('_', ' ', $order->order_type)),
            'table_number' => $order->table_number,
            'customer' => $order->customer ? [
                'id' => $order->customer->id,
                'name' => $order->customer->name,
                'phone' => $order->customer->phone,
                'points_earned' => (int) floor((float) $order->total_amount),
                'points_balance' => $order->customer->points_balance,
            ] : null,
            'items' => $order->items->map(function ($item) {
                return [
                    'name' => $item->item_name,
                    'variant' => $item->variant_name,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                    'notes' => $item->preparation_notes,
                    'addons' => $item->addons->map(fn($a) => [
                        'name' => $a->addon_name,
                        'price' => (float) $a->price,
                    ]),
                ];
            }),
            'subtotal' => (float) $order->subtotal,
            'discount_amount' => (float) $order->discount_amount,
            'tax_amount' => 0.0,
            'total_amount' => (float) $order->total_amount,
            'payments' => $order->payments->map(function ($p) {
                return [
                    'method' => ucfirst($p->payment_method),
                    'amount' => (float) $p->amount,
                    'cash_received' => $p->cash_received ? (float) $p->cash_received : null,
                    'cash_change' => $p->cash_change ? (float) $p->cash_change : null,
                    'reference' => $p->transaction_reference,
                ];
            }),
            'cafe_name' => SystemSetting::get('cafe_name', 'Artisan Roastery & Café'),
            'cafe_address' => SystemSetting::get('cafe_address', 'No. 88 Heritage Blvd'),
            'cafe_phone' => SystemSetting::get('cafe_phone', '+855 23 888 999'),
            'receipt_footer' => SystemSetting::get('receipt_footer', 'Thank you for your visit!'),
            'currency' => SystemSetting::get('currency_symbol', '$'),
        ];
    }

    public function receiptData(Order $order): JsonResponse
    {
        return response()->json($this->formatReceiptData($order));
    }

    public function searchCustomers(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));
        if (empty($q)) {
            $customers = Customer::where('is_active', true)->orderBy('name')->limit(20)->get();
        } else {
            $customers = Customer::where('is_active', true)
                ->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                          ->orWhere('phone', 'like', "%{$q}%")
                          ->orWhere('email', 'like', "%{$q}%")
                          ->orWhere('id', $q);
                })
                ->limit(20)
                ->get();
        }

        return response()->json(['customers' => $customers]);
    }

    public function quickCreateCustomer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30', 'unique:customers,phone'],
            'email' => ['nullable', 'email', 'max:120'],
        ]);

        $customer = Customer::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'points_balance' => 0,
            'total_spent' => 0.00,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'customer' => $customer,
            'message' => "Customer '{$customer->name}' enrolled successfully.",
        ]);
    }
}
