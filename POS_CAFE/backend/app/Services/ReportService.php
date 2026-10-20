<?php

namespace App\Services;

use App\Models\CashierShift;
use App\Models\Expense;
use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get financial analytics for given date range.
     */
    public function getFinancialSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        // Gross Sales: Paid orders sum
        $grossSales = (float) Order::whereBetween('created_at', [$start, $end])
            ->whereIn('payment_status', ['paid', 'partially_paid'])
            ->sum('total_amount');

        // Total orders count
        $orderCount = Order::whereBetween('created_at', [$start, $end])
            ->whereIn('payment_status', ['paid', 'partially_paid'])
            ->count();

        // Refunds
        $totalRefunds = (float) Refund::whereBetween('created_at', [$start, $end])->sum('amount');

        // Net Sales
        $netSales = max(0.0, round($grossSales - $totalRefunds, 2));

        // Average Order Value
        $averageOrderValue = $orderCount > 0 ? round($grossSales / $orderCount, 2) : 0.00;

        // Cost of Goods Sold (COGS) from inventory deductions
        $cogs = (float) InventoryMovement::whereBetween('created_at', [$start, $end])
            ->where('movement_type', 'order_deduction')
            ->selectRaw('SUM(ABS(quantity) * unit_cost) as total_cogs')
            ->value('total_cogs') ?? 0.00;

        // Operating Expenses (approved)
        $operatingExpenses = (float) Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->where('is_approved', true)
            ->sum('amount');

        // Gross Profit = Net Sales - COGS
        $grossProfit = round($netSales - $cogs, 2);

        // Operating Profit = Gross Profit - Operating Expenses
        $operatingProfit = round($grossProfit - $operatingExpenses, 2);

        // Total Inventory Current Value
        $inventoryValue = (float) Ingredient::where('is_active', true)
            ->selectRaw('SUM(current_stock * cost_per_unit) as val')
            ->value('val') ?? 0.00;

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'gross_sales' => round($grossSales, 2),
            'total_refunds' => round($totalRefunds, 2),
            'net_sales' => $netSales,
            'order_count' => $orderCount,
            'average_order_value' => $averageOrderValue,
            'cogs' => round($cogs, 2),
            'operating_expenses' => round($operatingExpenses, 2),
            'gross_profit' => $grossProfit,
            'operating_profit' => $operatingProfit,
            'inventory_value' => round($inventoryValue, 2),
        ];
    }

    /**
     * Sales trends for daily Chart.js chart (last 7 or 30 days).
     */
    public function getSalesTrend(int $days = 7): array
    {
        $startDate = now()->subDays($days - 1)->startOfDay();

        $dailySales = Order::where('created_at', '>=', $startDate)
            ->whereIn('payment_status', ['paid', 'partially_paid'])
            ->groupBy(DB::raw("DATE(created_at)"))
            ->select(
                DB::raw("DATE(created_at) as order_date"),
                DB::raw("SUM(total_amount) as daily_total"),
                DB::raw("COUNT(id) as count")
            )
            ->orderBy('order_date')
            ->pluck('daily_total', 'order_date')
            ->toArray();

        $labels = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $data[] = isset($dailySales[$date]) ? (float) $dailySales[$date] : 0.00;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Sales by payment method breakdown for doughnut chart.
     */
    public function getSalesByPaymentMethod(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        $payments = Payment::whereBetween('created_at', [$start, $end])
            ->where('status', 'successful')
            ->groupBy('payment_method')
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->get();

        $labels = [];
        $data = [];

        foreach ($payments as $payment) {
            $labels[] = ucfirst(str_replace('_', ' ', $payment->payment_method));
            $data[] = (float) $payment->total;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Best-selling products.
     */
    public function getTopProducts(int $limit = 5): array
    {
        return OrderItem::groupBy('product_id', 'item_name')
            ->select('item_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Sales by Cashier.
     */
    public function getSalesByCashier(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        return Order::with('cashier')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('payment_status', ['paid', 'partially_paid'])
            ->groupBy('user_id')
            ->select('user_id', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(total_amount) as total_sales'))
            ->orderByDesc('total_sales')
            ->get()
            ->map(function ($row) {
                return [
                    'cashier_name' => $row->cashier?->name ?? 'Unknown',
                    'orders_count' => $row->total_orders,
                    'total_sales' => (float) $row->total_sales,
                ];
            })
            ->toArray();
    }

    /**
     * Generate CSV export content for orders.
     */
    public function generateOrdersCsv(?string $startDate = null, ?string $endDate = null): string
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        $orders = Order::with(['cashier', 'customer', 'payments'])
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->get();

        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['Order Number', 'Date', 'Cashier', 'Customer', 'Type', 'Status', 'Payment Status', 'Subtotal', 'Discount', 'Tax', 'Total']);

        foreach ($orders as $order) {
            fputcsv($output, [
                $order->order_number,
                $order->created_at->format('Y-m-d H:i:s'),
                $order->cashier?->name ?? 'N/A',
                $order->customer?->name ?? 'Walk-in',
                $order->order_type,
                $order->status instanceof \BackedEnum ? $order->status->value : (string)$order->status,
                $order->payment_status instanceof \BackedEnum ? $order->payment_status->value : (string)$order->payment_status,
                $order->subtotal,
                $order->discount_amount,
                $order->tax_amount,
                $order->total_amount,
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
