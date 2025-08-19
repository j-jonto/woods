<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use App\Models\Item;
use App\Models\InventoryTransaction;
use App\Services\DoubleEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitabilityController extends Controller
{
    public function index()
    {
        $summary = $this->getProfitabilitySummary();
        
        return view('profitability.index', compact('summary'));
    }

    public function grossProfitReport(Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth());
        $toDate = $request->get('to_date', now()->endOfMonth());

        $sales = SalesOrder::with('items.item')
            ->whereBetween('order_date', [$fromDate, $toDate])
            ->where('status', 'invoiced')
            ->get();

        $grossProfitData = [];
        $totalSales = 0;
        $totalCost = 0;
        $totalGrossProfit = 0;

        foreach ($sales as $sale) {
            $saleCost = 0;
            $saleRevenue = $sale->total_amount;

            foreach ($sale->items as $item) {
                $cost = ($item->item->standard_cost ?? 0) * $item->quantity;
                $saleCost += $cost;
            }

            $grossProfit = $saleRevenue - $saleCost;
            $grossProfitMargin = $saleRevenue > 0 ? ($grossProfit / $saleRevenue) * 100 : 0;

            $grossProfitData[] = [
                'sale' => $sale,
                'revenue' => $saleRevenue,
                'cost' => $saleCost,
                'gross_profit' => $grossProfit,
                'gross_profit_margin' => $grossProfitMargin,
            ];

            $totalSales += $saleRevenue;
            $totalCost += $saleCost;
            $totalGrossProfit += $grossProfit;
        }

        $overallMargin = $totalSales > 0 ? ($totalGrossProfit / $totalSales) * 100 : 0;

        $summary = [
            'total_sales' => $totalSales,
            'total_cost' => $totalCost,
            'total_gross_profit' => $totalGrossProfit,
            'overall_margin' => $overallMargin,
        ];

        return view('profitability.gross_profit_report', compact('grossProfitData', 'summary', 'fromDate', 'toDate'));
    }

    public function productProfitabilityReport(Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth());
        $toDate = $request->get('to_date', now()->endOfMonth());

        $items = Item::with(['salesOrderItems' => function($query) use ($fromDate, $toDate) {
            $query->whereHas('salesOrder', function($q) use ($fromDate, $toDate) {
                $q->whereBetween('order_date', [$fromDate, $toDate])
                  ->where('status', 'invoiced');
            });
        }])->get();

        $productProfitability = [];
        $totalRevenue = 0;
        $totalCost = 0;
        $totalProfit = 0;

        foreach ($items as $item) {
            $quantity = $item->salesOrderItems->sum('quantity');
            $revenue = $item->salesOrderItems->sum(function($orderItem) {
                return $orderItem->quantity * $orderItem->unit_price;
            });
            $cost = $quantity * ($item->standard_cost ?? 0);
            $profit = $revenue - $cost;
            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

            if ($quantity > 0) {
                $productProfitability[] = [
                    'item' => $item,
                    'quantity' => $quantity,
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'profit' => $profit,
                    'margin' => $margin,
                    'avg_price' => $quantity > 0 ? $revenue / $quantity : 0,
                ];

                $totalRevenue += $revenue;
                $totalCost += $cost;
                $totalProfit += $profit;
            }
        }

        $overallMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        $summary = [
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'total_profit' => $totalProfit,
            'overall_margin' => $overallMargin,
        ];

        return view('profitability.product_profitability_report', compact('productProfitability', 'summary', 'fromDate', 'toDate'));
    }

    public function customerProfitabilityReport(Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth());
        $toDate = $request->get('to_date', now()->endOfMonth());

        $customers = \App\Models\Customer::with(['salesOrders' => function($query) use ($fromDate, $toDate) {
            $query->whereBetween('order_date', [$fromDate, $toDate])
                  ->where('status', 'invoiced');
        }, 'salesOrders.items.item'])->get();

        $customerProfitability = [];
        $totalRevenue = 0;
        $totalCost = 0;
        $totalProfit = 0;

        foreach ($customers as $customer) {
            $revenue = $customer->salesOrders->sum('total_amount');
            $cost = 0;

            foreach ($customer->salesOrders as $sale) {
                foreach ($sale->items as $item) {
                    $cost += ($item->item->standard_cost ?? 0) * $item->quantity;
                }
            }

            $profit = $revenue - $cost;
            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

            if ($revenue > 0) {
                $customerProfitability[] = [
                    'customer' => $customer,
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'profit' => $profit,
                    'margin' => $margin,
                    'order_count' => $customer->salesOrders->count(),
                ];

                $totalRevenue += $revenue;
                $totalCost += $cost;
                $totalProfit += $profit;
            }
        }

        $overallMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        $summary = [
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'total_profit' => $totalProfit,
            'overall_margin' => $overallMargin,
        ];

        return view('profitability.customer_profitability_report', compact('customerProfitability', 'summary', 'fromDate', 'toDate'));
    }

    public function netProfitReport(Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth());
        $toDate = $request->get('to_date', now()->endOfMonth());

        // اجلب بنود GL للفترة (posted فقط)
        $items = \App\Models\JournalEntryItem::with('account')
            ->whereHas('journalEntry', function($q) use ($fromDate, $toDate) {
                $q->whereBetween('entry_date', [$fromDate, $toDate])
                  ->where('status', 'posted');
            })
            ->whereHas('account', function($q) {
                $q->whereIn('type', ['revenue', 'expense']);
            })
            ->get();

        $totalRevenue = 0.0;
        $totalExpenses = 0.0;
        $revenueDetails = [];
        $expenseDetails = [];

        foreach ($items as $it) {
            $acc = $it->account;
            $debit = (float)$it->debit;
            $credit = (float)$it->credit;
            if ($acc->type === 'revenue') {
                $amount = $credit - $debit;
                $totalRevenue += $amount;
                $revenueDetails[$acc->code] = ($revenueDetails[$acc->code] ?? 0) + $amount;
            } elseif ($acc->type === 'expense') {
                $amount = $debit - $credit;
                $totalExpenses += $amount;
                $expenseDetails[$acc->code] = ($expenseDetails[$acc->code] ?? 0) + $amount;
            }
        }

        $grossProfit = $totalRevenue - $totalExpenses; // إذا كانت COGS ضمن المصروفات
        $netProfit = $grossProfit; // لا توجد بنود أخرى خارج المصروفات هنا

        $grossProfitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        $netProfitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;
        $expenseRatio = $totalRevenue > 0 ? ($totalExpenses / $totalRevenue) * 100 : 0;

        $summary = [
            'revenues' => $revenueDetails,
            'total_revenue' => $totalRevenue,
            'expenses' => $expenseDetails,
            'total_expenses' => $totalExpenses,
            'gross_profit' => $grossProfit,
            'gross_profit_margin' => $grossProfitMargin,
            'expense_ratio' => $expenseRatio,
            'net_profit' => $netProfit,
            'net_profit_margin' => $netProfitMargin,
        ];

        return view('profitability.net_profit_report', compact('summary', 'fromDate', 'toDate'));
    }

    public function netProfitDaily(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        return $this->netProfitOverRange($date, $date);
    }

    public function netProfitMonthly(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $from = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $to = (clone $from)->endOfMonth();
        return $this->netProfitOverRange($from, $to);
    }

    public function netProfitYearly(Request $request)
    {
        $year = (int)$request->get('year', now()->year);
        $from = \Carbon\Carbon::create($year, 1, 1)->startOfYear();
        $to = (clone $from)->endOfYear();
        return $this->netProfitOverRange($from, $to);
    }

    private function netProfitOverRange($fromDate, $toDate)
    {
        $req = new Request(['from_date' => $fromDate, 'to_date' => $toDate]);
        return $this->netProfitReport($req);
    }

    public function marginAnalysisReport(Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth());
        $toDate = $request->get('to_date', now()->endOfMonth());

        $items = Item::with(['salesOrderItems' => function($query) use ($fromDate, $toDate) {
            $query->whereHas('salesOrder', function($q) use ($fromDate, $toDate) {
                $q->whereBetween('order_date', [$fromDate, $toDate])
                  ->where('status', 'invoiced');
            });
        }])->get();

        $marginAnalysis = [];

        foreach ($items as $item) {
            $quantity = $item->salesOrderItems->sum('quantity');
            $revenue = $item->salesOrderItems->sum(function($orderItem) {
                return $orderItem->quantity * $orderItem->unit_price;
            });
            $cost = $quantity * ($item->standard_cost ?? 0);
            $profit = $revenue - $cost;
            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

            if ($quantity > 0) {
                $marginAnalysis[] = [
                    'item' => $item,
                    'quantity' => $quantity,
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'profit' => $profit,
                    'margin' => $margin,
                    'avg_price' => $quantity > 0 ? $revenue / $quantity : 0,
                    'avg_cost' => $item->standard_cost ?? 0,
                    'contribution' => $profit,
                ];
            }
        }

        // ترتيب حسب الربح
        usort($marginAnalysis, function($a, $b) {
            return $b['profit'] <=> $a['profit'];
        });

        $totalRevenue = collect($marginAnalysis)->sum('revenue');
        $totalCost = collect($marginAnalysis)->sum('cost');
        $totalProfit = collect($marginAnalysis)->sum('profit');
        $overallMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        $summary = [
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'total_profit' => $totalProfit,
            'overall_margin' => $overallMargin,
            'top_products' => array_slice($marginAnalysis, 0, 10),
            'low_margin_products' => array_filter($marginAnalysis, function($item) {
                return $item['margin'] < 20;
            }),
        ];

        return view('profitability.margin_analysis_report', compact('marginAnalysis', 'summary', 'fromDate', 'toDate'));
    }

    private function getProfitabilitySummary()
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        $currentMonthSales = SalesOrder::where('status', 'invoiced')
            ->whereBetween('order_date', [$currentMonth, now()])
            ->sum('total_amount');

        $lastMonthSales = SalesOrder::where('status', 'invoiced')
            ->whereBetween('order_date', [$lastMonth, $currentMonth])
            ->sum('total_amount');

        $salesGrowth = $lastMonthSales > 0 ? (($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100 : 0;

        return [
            'current_month_sales' => $currentMonthSales,
            'last_month_sales' => $lastMonthSales,
            'sales_growth' => $salesGrowth,
            'total_customers' => \App\Models\Customer::count(),
            'total_suppliers' => \App\Models\Supplier::count(),
            'active_products' => Item::where('is_active', true)->count(),
        ];
    }

    private function getRevenues($fromDate, $toDate)
    {
        return \App\Models\JournalEntryItem::with('account')
            ->whereHas('journalEntry', function($query) use ($fromDate, $toDate) {
                $query->whereBetween('entry_date', [$fromDate, $toDate])
                      ->where('is_posted', true);
            })
            ->whereHas('account', function($query) {
                $query->where('type', 'revenue');
            })
            ->where('entry_type', 'credit')
            ->get()
            ->groupBy('account_id')
            ->map(function($items, $accountId) {
                return [
                    'account' => $items->first()->account,
                    'amount' => $items->sum('amount'),
                ];
            });
    }

    private function getCostOfGoodsSold($fromDate, $toDate)
    {
        return \App\Models\JournalEntryItem::with('account')
            ->whereHas('journalEntry', function($query) use ($fromDate, $toDate) {
                $query->whereBetween('entry_date', [$fromDate, $toDate])
                      ->where('is_posted', true);
            })
            ->whereHas('account', function($query) {
                $query->where('type', 'expense')
                      ->where('code', 'like', '5%');
            })
            ->where('entry_type', 'debit')
            ->get()
            ->groupBy('account_id')
            ->map(function($items, $accountId) {
                return [
                    'account' => $items->first()->account,
                    'amount' => $items->sum('amount'),
                ];
            });
    }

    private function getExpenses($fromDate, $toDate)
    {
        return \App\Models\JournalEntryItem::with('account')
            ->whereHas('journalEntry', function($query) use ($fromDate, $toDate) {
                $query->whereBetween('entry_date', [$fromDate, $toDate])
                      ->where('is_posted', true);
            })
            ->whereHas('account', function($query) {
                $query->where('type', 'expense')
                      ->where('code', 'not like', '5%');
            })
            ->where('entry_type', 'debit')
            ->get()
            ->groupBy('account_id')
            ->map(function($items, $accountId) {
                return [
                    'account' => $items->first()->account,
                    'amount' => $items->sum('amount'),
                ];
            });
    }
} 