<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService) {}

    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $financials = $this->reportService->getFinancialSummary($startDate, $endDate);
        $salesTrend = $this->reportService->getSalesTrend(30);
        $paymentBreakdown = $this->reportService->getSalesByPaymentMethod($startDate, $endDate);
        $salesByCashier = $this->reportService->getSalesByCashier($startDate, $endDate);
        $topProducts = $this->reportService->getTopProducts(10);

        return view('reports.sales', compact(
            'startDate',
            'endDate',
            'financials',
            'salesTrend',
            'paymentBreakdown',
            'salesByCashier',
            'topProducts'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $csv = $this->reportService->generateOrdersCsv($startDate, $endDate);
        $filename = 'cafe-sales-report-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
