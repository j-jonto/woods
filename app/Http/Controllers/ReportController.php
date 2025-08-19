<?php
namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Customer;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\ProductionOrder;
use App\Models\Expense;
use App\Models\Revenue;
use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use App\Models\AuditLog;
use App\Models\AuditAlert;
use App\Models\RepresentativeTransaction;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // سيتم إضافة دوال التقارير هنا لاحقًا

    /**
     * تقرير تتبع حركة الصنف
     */
    public function itemMovement(Request $request)
    {
        $items = Item::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $query = InventoryTransaction::with(['item', 'warehouse', 'creator']);
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('from')) {
            $query->whereDate('transaction_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('transaction_date', '<=', $request->to);
        }
        $transactions = $query->orderByDesc('transaction_date')->paginate(20);
        return view('reports.item_movement', compact('items', 'warehouses', 'transactions'));
    }

    /**
     * تقارير المخزن (الكميات المتوفرة، الأصناف الناقصة، حركة المخزون)
     */
    public function warehouse(Request $request)
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $report_type = $request->get('report_type', 'stock');
        $warehouse_id = $request->get('warehouse_id');
        // تقرير الكميات المتوفرة
        if ($report_type === 'stock') {
            $stock = InventoryTransaction::selectRaw('item_id, warehouse_id, SUM(CASE WHEN type IN ("receipt", "transfer") THEN quantity ELSE -quantity END) as quantity')
                ->when($warehouse_id, function($q) use ($warehouse_id) { $q->where('warehouse_id', $warehouse_id); })
                ->groupBy('item_id', 'warehouse_id')
                ->with(['item', 'warehouse'])
                ->get();
            return view('reports.warehouse_report', [
                'warehouses' => $warehouses,
                'report_type' => $report_type,
                'stock' => $stock,
                'reorder' => [],
                'movements' => [],
            ]);
        }
        // تقرير الأصناف الناقصة
        if ($report_type === 'reorder') {
            $stock = InventoryTransaction::selectRaw('item_id, warehouse_id, SUM(CASE WHEN type IN ("receipt", "transfer") THEN quantity ELSE -quantity END) as quantity')
                ->when($warehouse_id, function($q) use ($warehouse_id) { $q->where('warehouse_id', $warehouse_id); })
                ->groupBy('item_id', 'warehouse_id')
                ->with(['item', 'warehouse'])
                ->get();
            $reorder = $stock->filter(function($row) {
                return $row->item && $row->quantity < $row->item->reorder_point;
            });
            return view('reports.warehouse_report', [
                'warehouses' => $warehouses,
                'report_type' => $report_type,
                'stock' => [],
                'reorder' => $reorder,
                'movements' => [],
            ]);
        }
        // تقرير حركة المخزون خلال فترة
        if ($report_type === 'movement') {
            $movements = InventoryTransaction::with(['item', 'warehouse'])
                ->when($warehouse_id, function($q) use ($warehouse_id) { $q->where('warehouse_id', $warehouse_id); })
                ->when($request->filled('from'), function($q) use ($request) { $q->whereDate('transaction_date', '>=', $request->from); })
                ->when($request->filled('to'), function($q) use ($request) { $q->whereDate('transaction_date', '<=', $request->to); })
                ->orderByDesc('transaction_date')
                ->get();
            return view('reports.warehouse_report', [
                'warehouses' => $warehouses,
                'report_type' => $report_type,
                'stock' => [],
                'reorder' => [],
                'movements' => $movements,
            ]);
        }
        // افتراضي
        return redirect()->route('reports.warehouse', ['report_type' => 'stock']);
    }

    /**
     * تقارير المبيعات (إجمالي/تفصيلي)
     */
    public function sales(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $report_type = $request->get('report_type', 'summary');
        $from = $request->get('from');
        $to = $request->get('to');
        $customer_id = $request->get('customer_id');
        $item_id = $request->get('item_id');
        // تقرير إجمالي
        if ($report_type === 'summary') {
            $summary = SalesOrder::with('customer')
                ->when($customer_id, function($q) use ($customer_id) { $q->where('customer_id', $customer_id); })
                ->when($from, function($q) use ($from) { $q->whereDate('order_date', '>=', $from); })
                ->when($to, function($q) use ($to) { $q->whereDate('order_date', '<=', $to); })
                ->selectRaw('customer_id, SUM(total_amount) as total_sales, COUNT(*) as orders_count')
                ->groupBy('customer_id')
                ->get();
            return view('reports.sales_report', [
                'customers' => $customers,
                'items' => $items,
                'report_type' => $report_type,
                'summary' => $summary,
                'details' => [],
            ]);
        }
        // تقرير تفصيلي
        if ($report_type === 'detail') {
            $details = SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
                ->join('customers', 'sales_orders.customer_id', '=', 'customers.id')
                ->join('items', 'sales_order_items.item_id', '=', 'items.id')
                ->when($customer_id, function($q) use ($customer_id) { $q->where('sales_orders.customer_id', $customer_id); })
                ->when($item_id, function($q) use ($item_id) { $q->where('sales_order_items.item_id', $item_id); })
                ->when($from, function($q) use ($from) { $q->whereDate('sales_orders.order_date', '>=', $from); })
                ->when($to, function($q) use ($to) { $q->whereDate('sales_orders.order_date', '<=', $to); })
                ->select([
                    'sales_orders.order_date',
                    'sales_orders.order_no',
                    'customers.name as customer_name',
                    'items.name as item_name',
                    'sales_order_items.quantity',
                    'sales_order_items.unit_price',
                    'sales_order_items.amount',
                ])
                ->orderByDesc('sales_orders.order_date')
                ->get();
            return view('reports.sales_report', [
                'customers' => $customers,
                'items' => $items,
                'report_type' => $report_type,
                'summary' => [],
                'details' => $details,
            ]);
        }
        // افتراضي
        return redirect()->route('reports.sales', ['report_type' => 'summary']);
    }

    /**
     * تقرير المشتريات
     */
    public function purchases(Request $request)
    {
        $suppliers = Supplier::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $report_type = $request->get('report_type', 'summary');
        $from = $request->get('from');
        $to = $request->get('to');
        $supplier_id = $request->get('supplier_id');
        $item_id = $request->get('item_id');

        if ($report_type === 'summary') {
            $summary = PurchaseOrder::with('supplier')
                ->when($supplier_id, function($q) use ($supplier_id) { $q->where('supplier_id', $supplier_id); })
                ->when($from, function($q) use ($from) { $q->whereDate('order_date', '>=', $from); })
                ->when($to, function($q) use ($to) { $q->whereDate('order_date', '<=', $to); })
                ->selectRaw('supplier_id, SUM(total_amount) as total_purchases, COUNT(*) as orders_count')
                ->groupBy('supplier_id')
                ->get();
            
            return view('reports.purchases_report', compact('suppliers', 'items', 'report_type', 'summary'));
        }

        if ($report_type === 'detail') {
            $details = PurchaseOrderItem::join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
                ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
                ->join('items', 'purchase_order_items.item_id', '=', 'items.id')
                ->when($supplier_id, function($q) use ($supplier_id) { $q->where('purchase_orders.supplier_id', $supplier_id); })
                ->when($item_id, function($q) use ($item_id) { $q->where('purchase_order_items.item_id', $item_id); })
                ->when($from, function($q) use ($from) { $q->whereDate('purchase_orders.order_date', '>=', $from); })
                ->when($to, function($q) use ($to) { $q->whereDate('purchase_orders.order_date', '<=', $to); })
                ->select([
                    'purchase_orders.order_date',
                    'purchase_orders.order_no',
                    'suppliers.name as supplier_name',
                    'items.name as item_name',
                    'purchase_order_items.quantity',
                    'purchase_order_items.unit_price',
                    'purchase_order_items.amount',
                ])
                ->orderByDesc('purchase_orders.order_date')
                ->get();
            
            return view('reports.purchases_report', compact('suppliers', 'items', 'report_type', 'details'));
        }

        return redirect()->route('reports.purchases', ['report_type' => 'summary']);
    }

    /**
     * تقرير المخزون
     */
    public function inventory(Request $request)
    {
        $items = Item::with('category')->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        
        $stockData = [];
        foreach ($items as $item) {
            $stockData[] = [
                'item' => $item->name,
                'category' => $item->category ? $item->category->name : 'غير محدد',
                'current_stock' => $item->getCurrentStock(),
                'stock_value' => $item->stock_value,
                'reorder_point' => $item->reorder_point,
                'is_low_stock' => $item->is_low_stock,
                'is_out_of_stock' => $item->is_out_of_stock,
            ];
        }

        return view('reports.inventory_report', compact('stockData', 'warehouses'));
    }

    /**
     * تقرير الإنتاج
     */
    public function production(Request $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        
        $productionOrders = ProductionOrder::with(['item', 'workCenter'])
            ->when($from, function($q) use ($from) { $q->whereDate('start_date', '>=', $from); })
            ->when($to, function($q) use ($to) { $q->whereDate('start_date', '<=', $to); })
            ->orderByDesc('start_date')
            ->get();

        $stats = [
            'total_orders' => $productionOrders->count(),
            'completed_orders' => $productionOrders->where('status', 'completed')->count(),
            'in_progress_orders' => $productionOrders->where('status', 'in_progress')->count(),
            'pending_orders' => $productionOrders->where('status', 'pending')->count(),
        ];

        return view('reports.production_report', compact('productionOrders', 'stats'));
    }

    /**
     * التقرير المالي
     */
    public function financial(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth());
        $to = $request->get('to', now()->endOfMonth());

        $sales = SalesOrder::whereBetween('order_date', [$from, $to])->sum('total_amount');
        $purchases = PurchaseOrder::whereBetween('order_date', [$from, $to])->sum('total_amount');
        $expenses = Expense::whereBetween('expense_date', [$from, $to])->sum('amount');
        $revenues = Revenue::whereBetween('revenue_date', [$from, $to])->sum('amount');

        // Add sales commissions to expenses
        $commissions = RepresentativeTransaction::where('type', 'commission')
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('amount');

        $totalExpenses = $expenses + $commissions;

        $financialData = [
            'sales' => $sales,
            'purchases' => $purchases,
            'expenses' => $totalExpenses,
            'revenues' => $revenues,
            'commissions' => $commissions, // Pass commissions separately for display if needed
            'gross_profit' => $sales - $purchases,
            'net_profit' => ($sales - $purchases) + $revenues - $totalExpenses,
        ];

        return view('reports.financial_report', compact('financialData', 'from', 'to'));
    }

    /**
     * تقرير الخزنة
     */
    public function treasury(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth());
        $to = $request->get('to', now()->endOfMonth());

        $transactions = TreasuryTransaction::with(['treasury', 'currency'])
            ->whereBetween('transaction_date', [$from, $to])
            ->orderByDesc('transaction_date')
            ->get();

        $summary = [
            'total_receipts' => $transactions->where('type', 'receipt')->sum('amount'),
            'total_payments' => $transactions->where('type', 'payment')->sum('amount'),
            'net_balance' => $transactions->where('type', 'receipt')->sum('amount') - $transactions->where('type', 'payment')->sum('amount'),
        ];

        return view('reports.treasury_report', compact('transactions', 'summary', 'from', 'to'));
    }

    /**
     * تقرير المصروفات
     */
    public function expenses(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth());
        $to = $request->get('to', now()->endOfMonth());
        $expense_type_id = $request->get('expense_type_id');

        $expenses = Expense::with('expenseType')
            ->when($expense_type_id, function($q) use ($expense_type_id) { $q->where('expense_type_id', $expense_type_id); })
            ->whereBetween('expense_date', [$from, $to])
            ->orderByDesc('expense_date')
            ->get();

        $summary = [
            'total_amount' => $expenses->sum('amount'),
            'by_type' => $expenses->groupBy('expenseType.name')->map->sum('amount'),
        ];

        return view('reports.expenses_report', compact('expenses', 'summary', 'from', 'to'));
    }

    /**
     * تقرير الإيرادات
     */
    public function revenues(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth());
        $to = $request->get('to', now()->endOfMonth());
        $revenue_type_id = $request->get('revenue_type_id');

        $revenues = Revenue::with('revenueType')
            ->when($revenue_type_id, function($q) use ($revenue_type_id) { $q->where('revenue_type_id', $revenue_type_id); })
            ->whereBetween('revenue_date', [$from, $to])
            ->orderByDesc('revenue_date')
            ->get();

        $summary = [
            'total_amount' => $revenues->sum('amount'),
            'by_type' => $revenues->groupBy('revenueType.name')->map->sum('amount'),
        ];

        return view('reports.revenues_report', compact('revenues', 'summary', 'from', 'to'));
    }

    /**
     * تقرير المراجعة
     */
    public function audit(Request $request)
    {
        $from = $request->get('from', now()->subDays(30));
        $to = $request->get('to', now());

        $logs = AuditLog::with('user')
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->get();

        $alerts = AuditAlert::whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->get();

        $summary = [
            'total_logs' => $logs->count(),
            'total_alerts' => $alerts->count(),
            'unresolved_alerts' => $alerts->where('resolved', false)->count(),
            'critical_alerts' => $alerts->where('severity', 'critical')->count(),
        ];

        return view('reports.audit_report', compact('logs', 'alerts', 'summary', 'from', 'to'));
    }

    /**
     * تقرير الأداء
     */
    public function performance(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth());
        $to = $request->get('to', now()->endOfMonth());

        $performanceData = [
            'sales_performance' => [
                'total_sales' => SalesOrder::whereBetween('order_date', [$from, $to])->sum('total_amount'),
                'orders_count' => SalesOrder::whereBetween('order_date', [$from, $to])->count(),
                'avg_order_value' => SalesOrder::whereBetween('order_date', [$from, $to])->avg('total_amount'),
            ],
            'production_performance' => [
                'total_orders' => ProductionOrder::whereBetween('start_date', [$from, $to])->count(),
                'completed_orders' => ProductionOrder::whereBetween('start_date', [$from, $to])->where('status', 'completed')->count(),
                'completion_rate' => ProductionOrder::whereBetween('start_date', [$from, $to])->where('status', 'completed')->count() / max(ProductionOrder::whereBetween('start_date', [$from, $to])->count(), 1) * 100,
            ],
            'inventory_performance' => [
                'low_stock_items' => Item::lowStock()->count(),
                'out_of_stock_items' => Item::outOfStock()->count(),
                'total_items' => Item::count(),
            ],
        ];

        return view('reports.performance_report', compact('performanceData', 'from', 'to'));
    }
} 