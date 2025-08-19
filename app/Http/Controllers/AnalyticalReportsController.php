<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\InventoryTransaction;
use App\Models\Treasury;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticalReportsController extends Controller
{
    public function index()
    {
        $summary = $this->getAnalyticalSummary();
        
        return view('analytical_reports.index', compact('summary'));
    }

    public function salesAnalysisReport(Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth());
        $toDate = $request->get('to_date', now()->endOfMonth());
        $groupBy = $request->get('group_by', 'daily'); // daily, weekly, monthly

        $sales = SalesOrder::with('items.item')
            ->whereBetween('order_date', [$fromDate, $toDate])
            ->where('status', 'invoiced')
            ->get();

        $analysis = $this->groupSalesData($sales, $groupBy);
        $summary = $this->getSalesSummary($sales);

        return view('analytical_reports.sales_analysis', compact('analysis', 'summary', 'fromDate', 'toDate', 'groupBy'));
    }

    public function inventoryAnalysisReport(Request $request)
    {
        $items = Item::with(['inventoryTransactions', 'salesOrderItems', 'purchaseOrderItems'])->get();

        $inventoryAnalysis = [];
        $totalValue = 0;
        $totalCost = 0;

        foreach ($items as $item) {
            $availableStock = $item->available_stock;
            $stockValue = $availableStock * ($item->standard_cost ?? 0);
            $avgCost = $item->standard_cost ?? 0;

            // حساب معدل الدوران
            $salesQuantity = $item->salesOrderItems->sum('quantity');
            $avgInventory = $availableStock > 0 ? $availableStock : 1;
            $turnoverRate = $salesQuantity / $avgInventory;

            // حساب أيام المخزون
            $daysOfInventory = $turnoverRate > 0 ? 365 / $turnoverRate : 0;

            $inventoryAnalysis[] = [
                'item' => $item,
                'available_stock' => $availableStock,
                'stock_value' => $stockValue,
                'avg_cost' => $avgCost,
                'turnover_rate' => $turnoverRate,
                'days_of_inventory' => $daysOfInventory,
                'sales_quantity' => $salesQuantity,
                'reorder_point' => $item->reorder_point ?? 0,
                'max_stock' => $item->max_stock ?? 0,
            ];

            $totalValue += $stockValue;
            $totalCost += $avgCost;
        }

        $summary = [
            'total_items' => $items->count(),
            'total_stock_value' => $totalValue,
            'avg_stock_value' => $items->count() > 0 ? $totalValue / $items->count() : 0,
            'items_low_stock' => $items->filter(function($item) {
                return $item->available_stock <= ($item->reorder_point ?? 0);
            })->count(),
            'items_out_of_stock' => $items->filter(function($item) {
                return $item->available_stock <= 0;
            })->count(),
        ];

        return view('analytical_reports.inventory_analysis', compact('inventoryAnalysis', 'summary'));
    }

    public function cashFlowAnalysisReport(Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth());
        $toDate = $request->get('to_date', now()->endOfMonth());

        $treasury = Treasury::first();
        if (!$treasury) {
            return back()->with('error', 'لا توجد خزنة متاحة');
        }

        $transactions = $treasury->transactions()
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->orderBy('transaction_date')
            ->get();

        $cashFlow = [
            'operating' => [
                'receipts' => 0,
                'payments' => 0,
                'net' => 0,
            ],
            'investing' => [
                'receipts' => 0,
                'payments' => 0,
                'net' => 0,
            ],
            'financing' => [
                'receipts' => 0,
                'payments' => 0,
                'net' => 0,
            ],
        ];

        foreach ($transactions as $transaction) {
            $category = $this->categorizeCashFlow($transaction);
            
            if ($transaction->type == 'receipt') {
                $cashFlow[$category]['receipts'] += $transaction->amount;
            } else {
                $cashFlow[$category]['payments'] += $transaction->amount;
            }
        }

        // حساب صافي التدفق لكل فئة
        foreach ($cashFlow as $category => $data) {
            $cashFlow[$category]['net'] = $data['receipts'] - $data['payments'];
        }

        $totalNetCashFlow = array_sum(array_column($cashFlow, 'net'));
        $openingBalance = $treasury->opening_balance;
        $closingBalance = $openingBalance + $totalNetCashFlow;

        $summary = [
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'total_net_cash_flow' => $totalNetCashFlow,
            'cash_flow' => $cashFlow,
        ];

        return view('analytical_reports.cash_flow_analysis', compact('summary', 'transactions', 'fromDate', 'toDate'));
    }

    public function customerAnalysisReport(Request $request)
    {
        $fromDate = $request->get('from_date', now()->subMonths(6));
        $toDate = $request->get('to_date', now());

        $customers = Customer::with(['salesOrders' => function($query) use ($fromDate, $toDate) {
            $query->whereBetween('order_date', [$fromDate, $toDate])
                  ->where('status', 'invoiced');
        }])->get();

        $customerAnalysis = [];
        $totalRevenue = 0;
        $totalOrders = 0;

        foreach ($customers as $customer) {
            $orders = $customer->salesOrders;
            $revenue = $orders->sum('total_amount');
            $orderCount = $orders->count();
            $avgOrderValue = $orderCount > 0 ? $revenue / $orderCount : 0;

            if ($revenue > 0) {
                $customerAnalysis[] = [
                    'customer' => $customer,
                    'revenue' => $revenue,
                    'order_count' => $orderCount,
                    'avg_order_value' => $avgOrderValue,
                    'last_order_date' => $orders->max('order_date'),
                    'payment_behavior' => $this->analyzePaymentBehavior($customer),
                ];

                $totalRevenue += $revenue;
                $totalOrders += $orderCount;
            }
        }

        // ترتيب حسب الإيرادات
        usort($customerAnalysis, function($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });

        $summary = [
            'total_customers' => $customers->count(),
            'active_customers' => count($customerAnalysis),
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'avg_revenue_per_customer' => count($customerAnalysis) > 0 ? $totalRevenue / count($customerAnalysis) : 0,
            'avg_order_value' => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0,
            'top_customers' => array_slice($customerAnalysis, 0, 10),
        ];

        return view('analytical_reports.customer_analysis', compact('customerAnalysis', 'summary', 'fromDate', 'toDate'));
    }

    public function supplierAnalysisReport(Request $request)
    {
        $fromDate = $request->get('from_date', now()->subMonths(6));
        $toDate = $request->get('to_date', now());

        $suppliers = Supplier::with(['purchaseOrders' => function($query) use ($fromDate, $toDate) {
            $query->whereBetween('order_date', [$fromDate, $toDate])
                  ->where('status', 'received');
        }])->get();

        $supplierAnalysis = [];
        $totalPurchases = 0;
        $totalOrders = 0;

        foreach ($suppliers as $supplier) {
            $orders = $supplier->purchaseOrders;
            $purchases = $orders->sum('total_amount');
            $orderCount = $orders->count();
            $avgOrderValue = $orderCount > 0 ? $purchases / $orderCount : 0;

            if ($purchases > 0) {
                $supplierAnalysis[] = [
                    'supplier' => $supplier,
                    'purchases' => $purchases,
                    'order_count' => $orderCount,
                    'avg_order_value' => $avgOrderValue,
                    'last_order_date' => $orders->max('order_date'),
                    'payment_behavior' => $this->analyzeSupplierPaymentBehavior($supplier),
                ];

                $totalPurchases += $purchases;
                $totalOrders += $orderCount;
            }
        }

        // ترتيب حسب المشتريات
        usort($supplierAnalysis, function($a, $b) {
            return $b['purchases'] <=> $a['purchases'];
        });

        $summary = [
            'total_suppliers' => $suppliers->count(),
            'active_suppliers' => count($supplierAnalysis),
            'total_purchases' => $totalPurchases,
            'total_orders' => $totalOrders,
            'avg_purchases_per_supplier' => count($supplierAnalysis) > 0 ? $totalPurchases / count($supplierAnalysis) : 0,
            'avg_order_value' => $totalOrders > 0 ? $totalPurchases / $totalOrders : 0,
            'top_suppliers' => array_slice($supplierAnalysis, 0, 10),
        ];

        return view('analytical_reports.supplier_analysis', compact('supplierAnalysis', 'summary', 'fromDate', 'toDate'));
    }

    public function trendAnalysisReport(Request $request)
    {
        $months = $request->get('months', 12);
        $endDate = now();
        $startDate = now()->subMonths($months);

        $trends = [];
        
        for ($i = 0; $i < $months; $i++) {
            $monthStart = $startDate->copy()->addMonths($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $monthlyData = [
                'month' => $monthStart->format('Y-m'),
                'sales' => SalesOrder::where('status', 'invoiced')
                    ->whereBetween('order_date', [$monthStart, $monthEnd])
                    ->sum('total_amount'),
                'purchases' => PurchaseOrder::where('status', 'received')
                    ->whereBetween('order_date', [$monthStart, $monthEnd])
                    ->sum('total_amount'),
                'orders' => SalesOrder::where('status', 'invoiced')
                    ->whereBetween('order_date', [$monthStart, $monthEnd])
                    ->count(),
                'customers' => SalesOrder::where('status', 'invoiced')
                    ->whereBetween('order_date', [$monthStart, $monthEnd])
                    ->distinct('customer_id')
                    ->count(),
            ];

            $trends[] = $monthlyData;
        }

        $summary = [
            'total_months' => $months,
            'avg_monthly_sales' => collect($trends)->avg('sales'),
            'avg_monthly_purchases' => collect($trends)->avg('purchases'),
            'avg_monthly_orders' => collect($trends)->avg('orders'),
            'growth_rate' => $this->calculateGrowthRate($trends),
        ];

        return view('analytical_reports.trend_analysis', compact('trends', 'summary'));
    }

    private function getAnalyticalSummary()
    {
        $currentMonth = now()->startOfMonth();
        
        return [
            'current_month_sales' => SalesOrder::where('status', 'invoiced')
                ->whereBetween('order_date', [$currentMonth, now()])
                ->sum('total_amount'),
            'current_month_purchases' => PurchaseOrder::where('status', 'received')
                ->whereBetween('order_date', [$currentMonth, now()])
                ->sum('total_amount'),
            'total_customers' => Customer::count(),
            'total_suppliers' => Supplier::count(),
            'total_products' => Item::count(),
            'treasury_balance' => Treasury::first()->current_balance ?? 0,
        ];
    }

    private function groupSalesData($sales, $groupBy)
    {
        $grouped = [];

        foreach ($sales as $sale) {
            switch ($groupBy) {
                case 'daily':
                    $key = $sale->order_date->format('Y-m-d');
                    break;
                case 'weekly':
                    $key = $sale->order_date->format('Y-W');
                    break;
                case 'monthly':
                    $key = $sale->order_date->format('Y-m');
                    break;
                default:
                    $key = $sale->order_date->format('Y-m-d');
            }

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'period' => $key,
                    'sales' => 0,
                    'orders' => 0,
                    'customers' => collect(),
                ];
            }

            $grouped[$key]['sales'] += $sale->total_amount;
            $grouped[$key]['orders']++;
            $grouped[$key]['customers']->push($sale->customer_id);
        }

        // تحويل العملاء إلى عدد فريد
        foreach ($grouped as &$group) {
            $group['customers'] = $group['customers']->unique()->count();
        }

        return $grouped;
    }

    private function getSalesSummary($sales)
    {
        return [
            'total_sales' => $sales->sum('total_amount'),
            'total_orders' => $sales->count(),
            'avg_order_value' => $sales->count() > 0 ? $sales->sum('total_amount') / $sales->count() : 0,
            'unique_customers' => $sales->pluck('customer_id')->unique()->count(),
            'cash_sales' => $sales->where('payment_type', 'cash')->sum('total_amount'),
            'credit_sales' => $sales->where('payment_type', 'credit')->sum('total_amount'),
        ];
    }

    private function categorizeCashFlow($transaction)
    {
        $description = strtolower($transaction->description);
        
        // التدفق التشغيلي
        if (str_contains($description, 'مبيعات') || 
            str_contains($description, 'مشتريات') || 
            str_contains($description, 'مصروفات') ||
            str_contains($description, 'إيرادات')) {
            return 'operating';
        }
        
        // التدفق الاستثماري
        if (str_contains($description, 'أصول') || 
            str_contains($description, 'معدات') ||
            str_contains($description, 'استثمار')) {
            return 'investing';
        }
        
        // التدفق التمويلي
        if (str_contains($description, 'قرض') || 
            str_contains($description, 'رأس مال') ||
            str_contains($description, 'سحب')) {
            return 'financing';
        }
        
        return 'operating';
    }

    private function analyzePaymentBehavior($customer)
    {
        $payments = $customer->payments()->orderBy('payment_date')->get();
        
        if ($payments->isEmpty()) {
            return 'no_payments';
        }
        
        $avgDaysToPay = $payments->avg(function($payment) {
            // حساب متوسط الأيام للدفع
            return 30; // تبسيط للحساب
        });
        
        if ($avgDaysToPay <= 15) {
            return 'excellent';
        } elseif ($avgDaysToPay <= 30) {
            return 'good';
        } elseif ($avgDaysToPay <= 60) {
            return 'fair';
        } else {
            return 'poor';
        }
    }

    private function analyzeSupplierPaymentBehavior($supplier)
    {
        $payments = $supplier->payments()->orderBy('payment_date')->get();
        
        if ($payments->isEmpty()) {
            return 'no_payments';
        }
        
        $avgDaysToPay = $payments->avg(function($payment) {
            // حساب متوسط الأيام للدفع
            return 30; // تبسيط للحساب
        });
        
        if ($avgDaysToPay <= 15) {
            return 'excellent';
        } elseif ($avgDaysToPay <= 30) {
            return 'good';
        } elseif ($avgDaysToPay <= 60) {
            return 'fair';
        } else {
            return 'poor';
        }
    }

    private function calculateGrowthRate($trends)
    {
        if (count($trends) < 2) {
            return 0;
        }
        
        $firstMonth = $trends[0]['sales'];
        $lastMonth = end($trends)['sales'];
        
        if ($firstMonth == 0) {
            return 0;
        }
        
        return (($lastMonth - $firstMonth) / $firstMonth) * 100;
    }
} 