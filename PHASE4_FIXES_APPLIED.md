# المرحلة الرابعة: تحسين التقارير

## الإصلاحات المطبقة

### 1. إنشاء controller لتقارير الأرصدة المدينة ✅

#### التغييرات:
- **ملف**: `app/Http/Controllers/AccountsReceivableController.php`
- **إضافة**: تقارير شاملة للأرصدة المدينة

#### الميزات:
- **تقرير الأرصدة المدينة**: عرض جميع العملاء الذين لديهم أرصدة
- **تقرير الأعمار**: تصنيف المستحقات حسب العمر (حالي، 30 يوم، 60 يوم، 90 يوم، أكثر من 90 يوم)
- **كشف حساب العميل**: تفاصيل كاملة لمعاملات العميل
- **تقرير المتأخرات**: العملاء الذين تجاوزوا مواعيد الدفع
- **تقرير التحصيل**: تحليل المدفوعات المستلمة
- **تصدير التقارير**: إمكانية تصدير التقارير بصيغ مختلفة

#### الكود المضاف:
```php
class AccountsReceivableController extends Controller
{
    public function agingReport(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now());
        
        $customers = Customer::with(['salesOrders', 'payments'])->get();
        $agingData = [];
        $totalByAge = [
            'current' => 0,
            '30_days' => 0,
            '60_days' => 0,
            '90_days' => 0,
            'over_90_days' => 0,
        ];

        foreach ($customers as $customer) {
            $aging = $this->calculateAging($customer, $asOfDate);
            
            if ($aging['total'] > 0) {
                $agingData[] = [
                    'customer' => $customer,
                    'aging' => $aging,
                ];

                $totalByAge['current'] += $aging['current'];
                $totalByAge['30_days'] += $aging['30_days'];
                $totalByAge['60_days'] += $aging['60_days'];
                $totalByAge['90_days'] += $aging['90_days'];
                $totalByAge['over_90_days'] += $aging['over_90_days'];
            }
        }

        return view('accounts_receivable.aging_report', compact('agingData', 'totalByAge', 'asOfDate'));
    }

    private function calculateAging($customer, $asOfDate)
    {
        $sales = $customer->salesOrders()
            ->where('order_date', '<=', $asOfDate)
            ->where('status', 'invoiced')
            ->where('payment_type', 'credit')
            ->get();

        $payments = $customer->payments()
            ->where('payment_date', '<=', $asOfDate)
            ->where('payment_type', 'payment')
            ->get();

        $aging = [
            'current' => 0,
            '30_days' => 0,
            '60_days' => 0,
            '90_days' => 0,
            'over_90_days' => 0,
            'total' => 0,
        ];

        foreach ($sales as $sale) {
            $daysOld = now()->diffInDays($sale->order_date);
            $amount = $sale->total_amount;

            if ($daysOld <= 30) {
                $aging['current'] += $amount;
            } elseif ($daysOld <= 60) {
                $aging['30_days'] += $amount;
            } elseif ($daysOld <= 90) {
                $aging['60_days'] += $amount;
            } elseif ($daysOld <= 120) {
                $aging['90_days'] += $amount;
            } else {
                $aging['over_90_days'] += $amount;
            }
        }

        // خصم المدفوعات
        $totalPayments = $payments->sum('amount');
        $aging['total'] = array_sum($aging) - $totalPayments;

        return $aging;
    }
}
```

### 2. إنشاء controller لتقارير الأرصدة الدائنة ✅

#### التغييرات:
- **ملف**: `app/Http/Controllers/AccountsPayableController.php`
- **إضافة**: تقارير شاملة للأرصدة الدائنة

#### الميزات:
- **تقرير الأرصدة الدائنة**: عرض جميع الموردين الذين لديهم أرصدة
- **تقرير الأعمار**: تصنيف المستحقات حسب العمر
- **كشف حساب المورد**: تفاصيل كاملة لمعاملات المورد
- **تقرير المتأخرات**: الموردين الذين تجاوزوا مواعيد الدفع
- **تقرير المدفوعات**: تحليل المدفوعات المقدمة
- **تقرير المدفوعات المعلقة**: المدفوعات التي لم تتم بعد

#### الكود المضاف:
```php
class AccountsPayableController extends Controller
{
    public function agingReport(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now());
        
        $suppliers = Supplier::with(['purchaseOrders', 'payments'])->get();
        $agingData = [];
        $totalByAge = [
            'current' => 0,
            '30_days' => 0,
            '60_days' => 0,
            '90_days' => 0,
            'over_90_days' => 0,
        ];

        foreach ($suppliers as $supplier) {
            $aging = $this->calculateAging($supplier, $asOfDate);
            
            if ($aging['total'] > 0) {
                $agingData[] = [
                    'supplier' => $supplier,
                    'aging' => $aging,
                ];

                $totalByAge['current'] += $aging['current'];
                $totalByAge['30_days'] += $aging['30_days'];
                $totalByAge['60_days'] += $aging['60_days'];
                $totalByAge['90_days'] += $aging['90_days'];
                $totalByAge['over_90_days'] += $aging['over_90_days'];
            }
        }

        return view('accounts_payable.aging_report', compact('agingData', 'totalByAge', 'asOfDate'));
    }

    public function pendingPaymentsReport()
    {
        $suppliers = Supplier::with(['purchaseOrders' => function($query) {
            $query->where('status', 'received')
                  ->where('payment_type', 'credit');
        }])->get();

        $pendingData = [];
        $totalPending = 0;

        foreach ($suppliers as $supplier) {
            $pending = $this->calculatePendingPayments($supplier);
            
            if ($pending['amount'] > 0) {
                $pendingData[] = [
                    'supplier' => $supplier,
                    'pending' => $pending,
                ];
                $totalPending += $pending['amount'];
            }
        }

        return view('accounts_payable.pending_payments_report', compact('pendingData', 'totalPending'));
    }
}
```

### 3. إنشاء controller لتقارير الربحية ✅

#### التغييرات:
- **ملف**: `app/Http/Controllers/ProfitabilityController.php`
- **إضافة**: تقارير شاملة للربحية

#### الميزات:
- **تقرير الربح الإجمالي**: تحليل الربح الإجمالي للمبيعات
- **تقرير ربحية المنتجات**: تحليل ربحية كل منتج
- **تقرير ربحية العملاء**: تحليل ربحية كل عميل
- **تقرير الربح الصافي**: تحليل شامل للربح الصافي
- **تقرير تحليل الهوامش**: تحليل هوامش الربح للمنتجات

#### الكود المضاف:
```php
class ProfitabilityController extends Controller
{
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
}
```

### 4. إنشاء controller للتقارير التحليلية ✅

#### التغييرات:
- **ملف**: `app/Http/Controllers/AnalyticalReportsController.php`
- **إضافة**: تقارير تحليلية شاملة

#### الميزات:
- **تقرير تحليل المبيعات**: تحليل المبيعات حسب الفترات الزمنية
- **تقرير تحليل المخزون**: تحليل المخزون ومعدلات الدوران
- **تقرير تحليل التدفق النقدي**: تحليل التدفق النقدي
- **تقرير تحليل العملاء**: تحليل سلوك العملاء
- **تقرير تحليل الموردين**: تحليل أداء الموردين
- **تقرير تحليل الاتجاهات**: تحليل الاتجاهات الزمنية

#### الكود المضاف:
```php
class AnalyticalReportsController extends Controller
{
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
}
```

### 5. إنشاء service للتقارير التحليلية ✅

#### التغييرات:
- **ملف**: `app/Services/ReportService.php`
- **إضافة**: service شامل للتقارير التحليلية

#### الميزات:
- **التقرير المالي الشامل**: تقرير شامل لجميع الجوانب المالية
- **تقرير مؤشرات الأداء**: حساب مؤشرات الأداء الرئيسية
- **تصدير التقارير**: إمكانية تصدير التقارير بصيغ مختلفة
- **حسابات تحليلية**: حسابات متقدمة للتحليل المالي

#### الكود المضاف:
```php
class ReportService
{
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

    private static function calculateGrowthRate($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return (($current - $previous) / $previous) * 100;
    }

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
}
```

## النتائج المتوقعة

### قبل الإصلاح:
- ❌ لا توجد تقارير للأرصدة المدينة والدائنة
- ❌ لا توجد تقارير للأعمار
- ❌ لا توجد تقارير للربحية
- ❌ لا توجد تقارير تحليلية
- ❌ لا توجد مؤشرات أداء

### بعد الإصلاح:
- ✅ تقارير شاملة للأرصدة المدينة والدائنة
- ✅ تقارير الأعمار للمستحقات
- ✅ تقارير الربحية التفصيلية
- ✅ تقارير تحليلية متقدمة
- ✅ مؤشرات أداء رئيسية
- ✅ إمكانية تصدير التقارير
- ✅ تحليل التدفق النقدي
- ✅ تحليل المخزون ومعدلات الدوران

## الاختبار المطلوب

### 1. اختبار تقارير الأرصدة المدينة:
1. إنشاء مبيعات آجلة
2. إنشاء مدفوعات من العملاء
3. التحقق من تقرير الأعمار
4. التحقق من كشف حساب العميل

### 2. اختبار تقارير الأرصدة الدائنة:
1. إنشاء مشتريات آجلة
2. إنشاء مدفوعات للموردين
3. التحقق من تقرير الأعمار
4. التحقق من تقرير المدفوعات المعلقة

### 3. اختبار تقارير الربحية:
1. إنشاء مبيعات ومشتريات
2. التحقق من تقرير الربح الإجمالي
3. التحقق من تقرير ربحية المنتجات
4. التحقق من تقرير ربحية العملاء

### 4. اختبار التقارير التحليلية:
1. التحقق من تقرير تحليل المبيعات
2. التحقق من تقرير تحليل المخزون
3. التحقق من تقرير التدفق النقدي
4. التحقق من مؤشرات الأداء

### 5. اختبار تصدير التقارير:
1. تصدير التقارير بصيغة JSON
2. تصدير التقارير بصيغة CSV
3. التحقق من دقة البيانات المصدرة

## الخطوات التالية

### المرحلة الخامسة: تحسينات إضافية
1. نظام الضرائب
2. نظام العملات المتعددة
3. نظام الفروع
4. نظام التدقيق والمراجعة

### المرحلة السادسة: تحسينات الواجهة
1. لوحات تحكم تفاعلية
2. رسوم بيانية متقدمة
3. تنبيهات تلقائية
4. تقارير مجدولة

## ملاحظات مهمة

1. **النسخ الاحتياطي**: تم الاحتفاظ بنسخ احتياطية قبل التطبيق
2. **التسجيل**: جميع الأخطاء مسجلة في ملفات السجل
3. **الاختبار**: يجب اختبار جميع الوظائف قبل الانتقال للمرحلة التالية
4. **التوثيق**: جميع التغييرات موثقة في هذا الملف
5. **التقارير**: تم إضافة تقارير شاملة ومتقدمة
6. **التحليل**: تم إضافة تحليلات مالية متقدمة
7. **مؤشرات الأداء**: تم إضافة مؤشرات أداء رئيسية
8. **التصدير**: تم إضافة إمكانية تصدير التقارير 