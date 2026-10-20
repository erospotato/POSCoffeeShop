<?php

namespace App\Http\Controllers\Pos;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\CheckoutRequest;
use App\Http\Requests\Pos\RefundRequest;
use App\Models\Category;
use App\Models\Customer;
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

        $taxRate = (float) SystemSetting::get('tax_rate', 10.0);
        $taxMode = SystemSetting::get('tax_mode', 'exclusive');
        $currency = SystemSetting::get('currency_symbol', '$');

        return view('pos.index', compact(
            'categories',
            'allProducts',
            'customers',
            'promotions',
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
        ]);

        try {
            $pricing = $this->posService->calculatePricing(
                $request->input('items'),
                $request->input('promotion_id'),
                $request->input('customer_id')
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

            return response()->json([
                'success' => true,
                'message' => 'Order created and payment processed successfully.',
                'order' => $order,
                'is_fully_paid' => $paymentResult['is_fully_paid'],
                'receipt_url' => route('pos.receipt', $order->id),
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
}
