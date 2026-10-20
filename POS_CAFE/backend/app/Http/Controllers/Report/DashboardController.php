<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Models\Expense;
use App\Models\Ingredient;
use App\Models\Order;
use App\Services\ReportService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected ReportService $reportService) {}

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard($request);
        }

        if ($user->isManager()) {
            return $this->managerDashboard($request);
        }

        if ($user->isCashier()) {
            return $this->cashierDashboard($request);
        }

        if ($user->isBarista()) {
            return $this->baristaDashboard($request);
        }

        return redirect()->route('pos.index');
    }

    protected function adminDashboard(Request $request)
    {
        $financials = $this->reportService->getFinancialSummary(
            $request->input('start_date'),
            $request->input('end_date')
        );

        $salesTrend = $this->reportService->getSalesTrend(7);
        $paymentBreakdown = $this->reportService->getSalesByPaymentMethod();
        $topProducts = $this->reportService->getTopProducts(5);
        $lowStockIngredients = Ingredient::where('is_active', true)
            ->whereColumn('current_stock', '<=', 'min_stock_alert')
            ->get();

        $recentOrders = Order::with(['cashier', 'customer'])
            ->latest()
            ->limit(6)
            ->get();

        $activeShifts = CashierShift::with('user')
            ->where('status', 'open')
            ->get();

        return view('dashboards.admin', compact(
            'financials',
            'salesTrend',
            'paymentBreakdown',
            'topProducts',
            'lowStockIngredients',
            'recentOrders',
            'activeShifts'
        ));
    }

    protected function managerDashboard(Request $request)
    {
        $todayFinancials = $this->reportService->getFinancialSummary(
            now()->toDateString(),
            now()->toDateString()
        );

        $salesTrend = $this->reportService->getSalesTrend(7);
        $topProducts = $this->reportService->getTopProducts(5);
        $lowStockIngredients = Ingredient::where('is_active', true)
            ->whereColumn('current_stock', '<=', 'min_stock_alert')
            ->get();

        $openShifts = CashierShift::with('user')->where('status', 'open')->get();
        $recentExpenses = Expense::with(['category', 'user'])->latest()->limit(5)->get();
        $kitchenOrdersCount = Order::whereIn('status', ['confirmed', 'preparing'])->count();

        return view('dashboards.manager', compact(
            'todayFinancials',
            'salesTrend',
            'topProducts',
            'lowStockIngredients',
            'openShifts',
            'recentExpenses',
            'kitchenOrdersCount'
        ));
    }

    protected function cashierDashboard(Request $request)
    {
        $user = $request->user();
        $activeShift = $user->activeShift();

        $todayOrders = Order::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->latest()
            ->limit(8)
            ->get();

        $todaySales = (float) Order::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->whereIn('payment_status', ['paid', 'partially_paid'])
            ->sum('total_amount');

        return view('dashboards.cashier', compact('activeShift', 'todayOrders', 'todaySales'));
    }

    protected function baristaDashboard(Request $request)
    {
        $queueOrders = Order::with(['items.addons', 'items.product'])
            ->whereIn('status', ['confirmed', 'preparing', 'ready'])
            ->orderBy('created_at')
            ->get();

        $pendingCount = $queueOrders->where('status', 'confirmed')->count();
        $preparingCount = $queueOrders->where('status', 'preparing')->count();
        $readyCount = $queueOrders->where('status', 'ready')->count();

        return view('dashboards.barista', compact(
            'queueOrders',
            'pendingCount',
            'preparingCount',
            'readyCount'
        ));
    }
}
