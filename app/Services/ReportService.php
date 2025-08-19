<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Treasury;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    /**
     * Generate comprehensive financial report
     */
    public static function generateFinancialReport($fromDate, $toDate)
    {
        $report = [
            'period' => [
                'from' => $fromDate,
                'to' => $toDate,
            ],
            'sales' => self::getSalesSummary($fromDate, $toDate),
            'purchases' => self::getPurchasesSummary($fromDate, $toDate),
            'profitability' => self::getProfitabilitySummary($fromDate, $toDate),
            'cash_flow' => self::getCashFlowSummary($fromDate, $toDate),
            'inventory' => self::getInventorySummary(),
            'customers' => self::getCustomersSummary($fromDate, $toDate),
            'suppliers' => self::getSuppliersSummary($fromDate, $toDate),
        ];

        return $report;
    }

    /**
     * Get sales summary
     */
    private static function getSalesSummary($fromDate, $toDate)
    {
        $sales = SalesOrder::where('status', 'invoiced')
            ->whereBetween('order_date', [$fromDate, $toDate])
            ->get();

        $cashSales = $sales->where('payment_type', 'cash');
        $creditSales = $sales->where('payment_type', 'credit');

        return [
            'total_sales' => $sales->sum('total_amount'),
            'total_orders' => $sales->count(),
            'cash_sales' => $cashSales->sum('total_amount'),
            'credit_sales' => $creditSales->sum('total_amount'),
            'avg_order_value' => $sales->count() > 0 ? $sales->sum('total_amount') / $sales->count() : 0,
            'unique_customers' => $sales->pluck('customer_id')->unique()->count(),
            'daily_average' => $sales->count() > 0 ? $sales->sum('total_amount') / $fromDate->diffInDays($toDate) : 0,
        ];
    }

    /**
     * Get purchases summary
     */
    private static function getPurchasesSummary($fromDate, $toDate)
    {
        $purchases = PurchaseOrder::where('status', 'received')
            ->whereBetween('order_date', [$fromDate, $toDate])
            ->get();

        $cashPurchases = $purchases->where('payment_type', 'cash');
        $creditPurchases = $purchases->where('payment_type', 'credit');

        return [
            'total_purchases' => $purchases->sum('total_amount'),
            'total_orders' => $purchases->count(),
            'cash_purchases' => $cashPurchases->sum('total_amount'),
            'credit_purchases' => $creditPurchases->sum('total_amount'),
            'avg_order_value' => $purchases->count() > 0 ? $purchases->sum('total_amount') / $purchases->count() : 0,
            'unique_suppliers' => $purchases->pluck('supplier_id')->unique()->count(),
            'daily_average' => $purchases->count() > 0 ? $purchases->sum('total_amount') / $fromDate->diffInDays($toDate) : 0,
        ];
    }

    /**
     * Get profitability summary
     */
    private static function getProfitabilitySummary($fromDate, $toDate)
    {
        $sales = SalesOrder::with('items.item')
            ->where('status', 'invoiced')
            ->whereBetween('order_date', [$fromDate, $toDate])
            ->get();

        $totalRevenue = $sales->sum('total_amount');
        $totalCost = 0;

        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $totalCost += ($item->item->standard_cost ?? 0) * $item->quantity;
            }
        }

        $grossProfit = $totalRevenue - $totalCost;
        $grossProfitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'gross_profit' => $grossProfit,
            'gross_profit_margin' => $grossProfitMargin,
            'net_profit_margin' => $grossProfitMargin, // تبسيط - بدون مصروفات
        ];
    }

    /**
     * Get cash flow summary
     */
    private static function getCashFlowSummary($fromDate, $toDate)
    {
        $treasury = Treasury::first();
        if (!$treasury) {
            return [
                'opening_balance' => 0,
                'closing_balance' => 0,
                'net_cash_flow' => 0,
                'receipts' => 0,
                'payments' => 0,
            ];
        }

        $transactions = $treasury->transactions()
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->get();

        $receipts = $transactions->where('type', 'receipt')->sum('amount');
        $payments = $transactions->where('type', 'payment')->sum('amount');
        $netCashFlow = $receipts - $payments;

        return [
            'opening_balance' => $treasury->opening_balance,
            'closing_balance' => $treasury->current_balance,
            'net_cash_flow' => $netCashFlow,
            'receipts' => $receipts,
            'payments' => $payments,
        ];
    }

    /**
     * Get inventory summary
     */
    private static function getInventorySummary()
    {
        $items = Item::all();
        $totalItems = $items->count();
        $activeItems = $items->where('is_active', true)->count();
        $totalStockValue = 0;
        $lowStockItems = 0;
        $outOfStockItems = 0;

        foreach ($items as $item) {
            $stockValue = $item->available_stock * ($item->standard_cost ?? 0);
            $totalStockValue += $stockValue;

            if ($item->available_stock <= 0) {
                $outOfStockItems++;
            } elseif ($item->available_stock <= ($item->reorder_point ?? 0)) {
                $lowStockItems++;
            }
        }

        return [
            'total_items' => $totalItems,
            'active_items' => $activeItems,
            'total_stock_value' => $totalStockValue,
            'low_stock_items' => $lowStockItems,
            'out_of_stock_items' => $outOfStockItems,
            'avg_stock_value' => $totalItems > 0 ? $totalStockValue / $totalItems : 0,
        ];
    }

    /**
     * Get customers summary
     */
    private static function getCustomersSummary($fromDate, $toDate)
    {
        $customers = Customer::all();
        $activeCustomers = SalesOrder::where('status', 'invoiced')
            ->whereBetween('order_date', [$fromDate, $toDate])
            ->pluck('customer_id')
            ->unique()
            ->count();

        $totalReceivables = $customers->sum('current_balance');
        $overdueCustomers = $customers->filter(function($customer) {
            return $customer->current_balance > ($customer->credit_limit ?? 0);
        })->count();

        return [
            'total_customers' => $customers->count(),
            'active_customers' => $activeCustomers,
            'total_receivables' => $totalReceivables,
            'overdue_customers' => $overdueCustomers,
            'avg_receivables_per_customer' => $customers->count() > 0 ? $totalReceivables / $customers->count() : 0,
        ];
    }

    /**
     * Get suppliers summary
     */
    private static function getSuppliersSummary($fromDate, $toDate)
    {
        $suppliers = Supplier::all();
        $activeSuppliers = PurchaseOrder::where('status', 'received')
            ->whereBetween('order_date', [$fromDate, $toDate])
            ->pluck('supplier_id')
            ->unique()
            ->count();

        $totalPayables = $suppliers->sum('current_balance');

        return [
            'total_suppliers' => $suppliers->count(),
            'active_suppliers' => $activeSuppliers,
            'total_payables' => $totalPayables,
            'avg_payables_per_supplier' => $suppliers->count() > 0 ? $totalPayables / $suppliers->count() : 0,
        ];
    }

    /**
     * Generate KPI report
     */
    public static function generateKPIReport($period = 'month')
    {
        $endDate = now();
        
        switch ($period) {
            case 'week':
                $startDate = $endDate->copy()->subWeek();
                break;
            case 'month':
                $startDate = $endDate->copy()->subMonth();
                break;
            case 'quarter':
                $startDate = $endDate->copy()->subQuarter();
                break;
            case 'year':
                $startDate = $endDate->copy()->subYear();
                break;
            default:
                $startDate = $endDate->copy()->subMonth();
        }

        $currentPeriod = self::generateFinancialReport($startDate, $endDate);
        $previousPeriod = self::generateFinancialReport($startDate->copy()->subPeriod($period), $startDate);

        $kpis = [
            'sales_growth' => self::calculateGrowthRate($currentPeriod['sales']['total_sales'], $previousPeriod['sales']['total_sales']),
            'profit_growth' => self::calculateGrowthRate($currentPeriod['profitability']['gross_profit'], $previousPeriod['profitability']['gross_profit']),
            'customer_growth' => self::calculateGrowthRate($currentPeriod['customers']['active_customers'], $previousPeriod['customers']['active_customers']),
            'inventory_turnover' => self::calculateInventoryTurnover($startDate, $endDate),
            'cash_flow_ratio' => self::calculateCashFlowRatio($currentPeriod['cash_flow']),
            'receivables_turnover' => self::calculateReceivablesTurnover($startDate, $endDate),
            'payables_turnover' => self::calculatePayablesTurnover($startDate, $endDate),
        ];

        return [
            'period' => $period,
            'current_period' => $currentPeriod,
            'previous_period' => $previousPeriod,
            'kpis' => $kpis,
        ];
    }

    /**
     * Calculate growth rate
     */
    private static function calculateGrowthRate($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return (($current - $previous) / $previous) * 100;
    }

    /**
     * Calculate inventory turnover
     */
    private static function calculateInventoryTurnover($startDate, $endDate)
    {
        $sales = SalesOrder::with('items.item')
            ->where('status', 'invoiced')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->get();

        $totalCost = 0;
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                $totalCost += ($item->item->standard_cost ?? 0) * $item->quantity;
            }
        }

        $avgInventory = Item::sum(DB::raw('available_stock * COALESCE(standard_cost, 0)'));
        
        return $avgInventory > 0 ? $totalCost / $avgInventory : 0;
    }

    /**
     * Calculate cash flow ratio
     */
    private static function calculateCashFlowRatio($cashFlow)
    {
        $totalPayments = $cashFlow['payments'];
        
        return $totalPayments > 0 ? $cashFlow['receipts'] / $totalPayments : 0;
    }

    /**
     * Calculate receivables turnover
     */
    private static function calculateReceivablesTurnover($startDate, $endDate)
    {
        $creditSales = SalesOrder::where('status', 'invoiced')
            ->where('payment_type', 'credit')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->sum('total_amount');

        $avgReceivables = Customer::avg('current_balance');
        
        return $avgReceivables > 0 ? $creditSales / $avgReceivables : 0;
    }

    /**
     * Calculate payables turnover
     */
    private static function calculatePayablesTurnover($startDate, $endDate)
    {
        $creditPurchases = PurchaseOrder::where('status', 'received')
            ->where('payment_type', 'credit')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->sum('total_amount');

        $avgPayables = Supplier::avg('current_balance');
        
        return $avgPayables > 0 ? $creditPurchases / $avgPayables : 0;
    }

    /**
     * Export report to Excel/CSV
     */
    public static function exportReport($report, $format = 'json')
    {
        switch ($format) {
            case 'json':
                return json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            case 'csv':
                return self::convertToCSV($report);
            case 'excel':
                return self::convertToExcel($report);
            default:
                return json_encode($report);
        }
    }

    /**
     * Convert report to CSV
     */
    private static function convertToCSV($report)
    {
        // تبسيط - إرجاع JSON كـ CSV
        $csv = "Metric,Value\n";
        $csv .= "Total Sales," . $report['sales']['total_sales'] . "\n";
        $csv .= "Total Purchases," . $report['purchases']['total_purchases'] . "\n";
        $csv .= "Gross Profit," . $report['profitability']['gross_profit'] . "\n";
        
        return $csv;
    }

    /**
     * Convert report to Excel
     */
    private static function convertToExcel($report)
    {
        // تبسيط - إرجاع JSON
        return json_encode($report);
    }
} 