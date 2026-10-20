<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyTransaction;
use App\Models\Product;
use App\Services\AuditService;
use App\Services\LoyaltyService;
use Exception;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function __construct(protected LoyaltyService $loyaltyService) {}

    public function index()
    {
        $rewards = LoyaltyReward::with('product')->get();
        $recentTransactions = LoyaltyTransaction::with(['customer', 'order'])->latest()->paginate(15);
        $products = Product::where('is_available', true)->orderBy('name')->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        return view('loyalty.index', compact('rewards', 'recentTransactions', 'products', 'customers'));
    }

    public function storeReward(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'points_required' => ['required', 'integer', 'min:1'],
            'discount_type' => ['required', 'in:fixed_amount,percentage,free_product'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'product_id' => ['nullable', 'exists:products,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $reward = LoyaltyReward::create($data);

        AuditService::log('loyalty_reward_created', $reward);

        return back()->with('success', "Reward '{$reward->name}' created.");
    }

    public function redeem(Request $request)
    {
        $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'reward_id' => ['required', 'exists:loyalty_rewards,id'],
        ]);

        $customer = Customer::findOrFail($request->input('customer_id'));
        $reward = LoyaltyReward::findOrFail($request->input('reward_id'));

        try {
            $txn = $this->loyaltyService->redeemReward($customer, $reward);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'transaction' => $txn, 'remaining_points' => $customer->fresh()->points_balance]);
            }

            return back()->with('success', "Redeemed '{$reward->name}' for {$customer->name}. Remaining points: {$customer->fresh()->points_balance}");
        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['loyalty_error' => $e->getMessage()]);
        }
    }
}
