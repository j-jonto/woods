<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use App\Models\InventoryTransaction;
use App\Models\ProductionOrder;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Treasury;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\AuditLog;
use App\Models\AuditAlert;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // البيانات الأساسية
        $totalSales = SalesOrder::where('status', 'invoiced')->sum('total_amount');
        $totalPurchases = PurchaseOrder::where('status', 'received')->sum('total_amount');
        $inventoryCount = Item::count();
        $productionInProgress = ProductionOrder::where('status', 'in_progress')->count();
        $customerCount = Customer::count();
        $supplierCount = Supplier::count();
        
        // البيانات الحديثة
        $recentSales = SalesOrder::with(['customer', 'currency'])->orderByDesc('order_date')->take(5)->get();
        $recentPurchases = PurchaseOrder::with(['supplier', 'currency'])->orderByDesc('order_date')->take(5)->get();
        
        // معلومات الخزنة
        $treasury = Treasury::first();
        $treasuryBalance = $treasury ? $treasury->current_balance : 0;
        $treasurySummary = $treasury ? $treasury->getSummary() : null;
        
        // بيانات الفروع
        $branches = Branch::active()->with('treasury')->get();
        $branchStats = $this->getBranchStatistics();
        
        // بيانات العملات
        $currencies = Currency::active()->get();
        $currencyStats = $this->getCurrencyStatistics();
        
        // بيانات التدقيق
        $auditStats = $this->getAuditStatistics();
        
        // الرسوم البيانية
        $salesChart = $this->getSalesChart();
        $purchasesChart = $this->getPurchasesChart();
        $inventoryChart = $this->getInventoryChart();
        $treasuryChart = $this->getTreasuryChart();
        
        // التنبيهات
        $alerts = $this->getSystemAlerts();
        
        // الإحصائيات الشهرية
        $monthlyStats = $this->getMonthlyStatistics();
        
        return view('dashboard.index', compact(
            'totalSales', 'totalPurchases', 'inventoryCount', 'productionInProgress',
            'customerCount', 'supplierCount', 'recentSales', 'recentPurchases',
            'treasuryBalance', 'treasurySummary', 'branches', 'branchStats',
            'currencies', 'currencyStats', 'auditStats', 'salesChart', 'purchasesChart',
            'inventoryChart', 'treasuryChart', 'alerts', 'monthlyStats'
        ));
    }

    /**
     * الحصول على إحصائيات الفروع
     */
    private function getBranchStatistics()
    {
        $branches = Branch::active()->get();
        $stats = [];
        
        foreach ($branches as $branch) {
            $stats[] = [
                'name' => $branch->name,
                'sales' => $branch->getMonthlySales(date('Y'), date('m')),
                'purchases' => $branch->getMonthlyPurchases(date('Y'), date('m')),
                'customers' => $branch->customers()->count(),
                'suppliers' => $branch->suppliers()->count(),
                'treasury_balance' => $branch->treasury ? $branch->treasury->current_balance : 0,
            ];
        }
        
        return $stats;
    }

    /**
     * الحصول على إحصائيات العملات
     */
    private function getCurrencyStatistics()
    {
        $currencies = Currency::active()->get();
        $stats = [];
        
        foreach ($currencies as $currency) {
            $stats[] = [
                'name' => $currency->name,
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'exchange_rate' => $currency->exchange_rate,
                'total_transactions' => $currency->salesOrders()->count() + $currency->purchaseOrders()->count(),
            ];
        }
        
        return $stats;
    }

    /**
     * الحصول على إحصائيات التدقيق
     */
    private function getAuditStatistics()
    {
        $today = Carbon::today();
        $weekAgo = Carbon::now()->subWeek();
        $monthAgo = Carbon::now()->subMonth();
        
        return [
            'today_events' => AuditLog::whereDate('created_at', $today)->count(),
            'week_events' => AuditLog::where('created_at', '>=', $weekAgo)->count(),
            'month_events' => AuditLog::where('created_at', '>=', $monthAgo)->count(),
            'unresolved_alerts' => AuditAlert::where('is_resolved', false)->count(),
            'critical_alerts' => AuditAlert::where('severity', 'critical')->where('is_resolved', false)->count(),
            'security_alerts' => AuditAlert::where('alert_type', 'security')->where('is_resolved', false)->count(),
        ];
    }

    /**
     * الحصول على بيانات رسم المبيعات
     */
    private function getSalesChart()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthSales = SalesOrder::whereYear('order_date', $date->year)
                ->whereMonth('order_date', $date->month)
                ->where('status', 'invoiced')
                ->sum('total_amount');
            
            $data[] = [
                'month' => $date->format('M Y'),
                'sales' => $monthSales,
            ];
        }
        
        return $data;
    }

    /**
     * الحصول على بيانات رسم المشتريات
     */
    private function getPurchasesChart()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthPurchases = PurchaseOrder::whereYear('order_date', $date->year)
                ->whereMonth('order_date', $date->month)
                ->where('status', 'received')
                ->sum('total_amount');
            
            $data[] = [
                'month' => $date->format('M Y'),
                'purchases' => $monthPurchases,
            ];
        }
        
        return $data;
    }

    /**
     * الحصول على بيانات رسم المخزون
     */
    private function getInventoryChart()
    {
        $items = Item::with('category')->get();
        $data = [];
        
        foreach ($items as $item) {
            $currentStock = $item->getCurrentStock();
            $data[] = [
                'item' => $item->name,
                'stock' => $currentStock,
                'category' => $item->category ? $item->category->name : 'غير محدد',
            ];
        }
        
        return $data;
    }

    /**
     * الحصول على بيانات رسم الخزنة
     */
    private function getTreasuryChart()
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            // هنا يمكن إضافة منطق لحساب رصيد الخزنة لكل يوم
            $data[] = [
                'date' => $date->format('M d'),
                'balance' => rand(10000, 50000), // بيانات تجريبية
            ];
        }
        
        return $data;
    }

    /**
     * الحصول على تنبيهات النظام
     */
    private function getSystemAlerts()
    {
        $alerts = [];
        
        // تنبيهات المخزون
        $lowStockItems = Item::where('minimum_stock', '>', 0)
            ->get()
            ->filter(function ($item) {
                return $item->getCurrentStock() <= $item->minimum_stock;
            });
        
        if ($lowStockItems->count() > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'مخزون منخفض',
                'message' => "هناك {$lowStockItems->count()} منتج بمخزون منخفض",
                'icon' => 'fas fa-exclamation-triangle',
            ];
        }
        
        // تنبيهات العملاء
        $overdueCustomers = Customer::where('current_balance', '>', 0)
            ->where('credit_limit', '>', 0)
            ->get()
            ->filter(function ($customer) {
                return $customer->current_balance > $customer->credit_limit;
            });
        
        if ($overdueCustomers->count() > 0) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'عملاء متجاوزون للحد الائتماني',
                'message' => "هناك {$overdueCustomers->count()} عميل متجاوز للحد الائتماني",
                'icon' => 'fas fa-user-times',
            ];
        }
        
        // تنبيهات الموردين
        $overdueSuppliers = Supplier::where('current_balance', '>', 0)
            ->get()
            ->filter(function ($supplier) {
                return $supplier->current_balance > 10000; // مثال
            });
        
        if ($overdueSuppliers->count() > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'مدفوعات للموردين',
                'message' => "هناك {$overdueSuppliers->count()} مورد يحتاج إلى دفع",
                'icon' => 'fas fa-money-bill-wave',
            ];
        }
        
        return $alerts;
    }

    /**
     * الحصول على الإحصائيات الشهرية
     */
    private function getMonthlyStatistics()
    {
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();
        
        $currentMonthSales = SalesOrder::whereYear('order_date', $currentMonth->year)
            ->whereMonth('order_date', $currentMonth->month)
            ->where('status', 'invoiced')
            ->sum('total_amount');
        
        $lastMonthSales = SalesOrder::whereYear('order_date', $lastMonth->year)
            ->whereMonth('order_date', $lastMonth->month)
            ->where('status', 'invoiced')
            ->sum('total_amount');
        
        $salesGrowth = $lastMonthSales > 0 ? (($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100 : 0;
        
        $currentMonthPurchases = PurchaseOrder::whereYear('order_date', $currentMonth->year)
            ->whereMonth('order_date', $currentMonth->month)
            ->where('status', 'received')
            ->sum('total_amount');
        
        $lastMonthPurchases = PurchaseOrder::whereYear('order_date', $lastMonth->year)
            ->whereMonth('order_date', $lastMonth->month)
            ->where('status', 'received')
            ->sum('total_amount');
        
        $purchasesGrowth = $lastMonthPurchases > 0 ? (($currentMonthPurchases - $lastMonthPurchases) / $lastMonthPurchases) * 100 : 0;
        
        return [
            'current_month_sales' => $currentMonthSales,
            'last_month_sales' => $lastMonthSales,
            'sales_growth' => $salesGrowth,
            'current_month_purchases' => $currentMonthPurchases,
            'last_month_purchases' => $lastMonthPurchases,
            'purchases_growth' => $purchasesGrowth,
        ];
    }

    /**
     * API للحصول على بيانات الرسم البياني
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'sales');
        $period = $request->get('period', 'monthly');
        
        switch ($type) {
            case 'sales':
                return response()->json($this->getSalesChart());
            case 'purchases':
                return response()->json($this->getPurchasesChart());
            case 'inventory':
                return response()->json($this->getInventoryChart());
            case 'treasury':
                return response()->json($this->getTreasuryChart());
            default:
                return response()->json([]);
        }
    }

    /**
     * API للحصول على التنبيهات
     */
    public function getAlerts()
    {
        return response()->json($this->getSystemAlerts());
    }

    /**
     * API للحصول على الإحصائيات المحدثة
     */
    public function getStats()
    {
        $stats = [
            'total_sales' => SalesOrder::where('status', 'invoiced')->sum('total_amount'),
            'total_purchases' => PurchaseOrder::where('status', 'received')->sum('total_amount'),
            'inventory_count' => Item::count(),
            'production_in_progress' => ProductionOrder::where('status', 'in_progress')->count(),
            'customer_count' => Customer::count(),
            'supplier_count' => Supplier::count(),
            'treasury_balance' => Treasury::first() ? Treasury::first()->current_balance : 0,
        ];
        
        return response()->json($stats);
    }
} 