<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\InventoryTransactionController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\ProductionOrderController;
use App\Http\Controllers\BillOfMaterialController;
use App\Http\Controllers\WorkCenterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\FixedAssetController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ProfitabilityController;

// تفعيل مسارات المصادقة مرة واحدة فقط
Auth::routes();

// إعادة توجيه الصفحة الرئيسية إلى لوحة التحكم أو تسجيل الدخول
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    } else {
        return redirect()->route('login');
    }
});

// لوحة التحكم - متاحة لجميع المستخدمين المسجلين
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
    Route::get('/dashboard/alerts', [DashboardController::class, 'getAlerts'])->name('dashboard.alerts');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
});

// إدارة المستخدمين والأدوار - للمدير فقط
Route::middleware(['auth', 'permission:manage users'])->group(function () {
    Route::resource('users', UserController::class);
});

// إدارة دليل الحسابات والقيود اليومية - للمحاسب والمدير
Route::middleware(['auth', 'permission:manage coa'])->group(function () {
    Route::resource('coa', ChartOfAccountController::class);
});

Route::middleware(['auth', 'permission:manage journal entries'])->group(function () {
    Route::resource('journal_entries', JournalEntryController::class);
    Route::post('journal_entries/{journalEntry}/post', [JournalEntryController::class, 'post'])->name('journal_entries.post');
});

// إدارة المخزون - لمدير الإنتاج وموظف المخزون والمدير
Route::middleware(['auth', 'permission:manage inventory'])->group(function () {
    Route::resource('inventory_transactions', InventoryTransactionController::class);
});

Route::middleware(['auth', 'permission:manage items'])->group(function () {
    Route::resource('items', ItemController::class);
    Route::resource('item_categories', ItemCategoryController::class);
});

Route::middleware(['auth', 'permission:manage warehouses'])->group(function () {
    Route::resource('warehouses', WarehouseController::class);
});

// إدارة الإنتاج - لمدير الإنتاج والمدير
Route::middleware(['auth', 'permission:manage production'])->group(function () {
    Route::resource('production_orders', ProductionOrderController::class);
    Route::resource('boms', BillOfMaterialController::class);
    Route::resource('work_centers', WorkCenterController::class);
});

// إدارة المبيعات - لموظف المبيعات والمدير
Route::middleware(['auth', 'permission:manage sales'])->group(function () {
    Route::resource('customers', CustomerController::class);
    Route::resource('sales_orders', SalesOrderController::class);
    Route::put('sales_orders/{salesOrder}/update-status', [SalesOrderController::class, 'updateStatus'])->name('sales_orders.update_status');
});

// إدارة المشتريات - للمدير
Route::middleware(['auth', 'permission:manage purchasing'])->group(function () {
    Route::resource('suppliers', SupplierController::class);
    Route::resource('purchase_orders', PurchaseOrderController::class);
});

// إدارة الأصول الثابتة - للمدير
Route::middleware(['auth', 'permission:manage fixed assets'])->group(function () {
    Route::resource('fixed_assets', FixedAssetController::class);
    Route::put('fixed_assets/{fixedAsset}/dispose', [FixedAssetController::class, 'dispose'])->name('fixed_assets.dispose');
});

// سجلات التدقيق - للمدير فقط
Route::middleware(['auth', 'permission:manage audit logs'])->group(function () {
    Route::resource('audit_logs', AuditLogController::class)->only(['index', 'show']);
});

// إعادة توجيه /home إلى لوحة التحكم (dashboard)
Route::get('/home', function () {
    return redirect()->route('dashboard');
})->name('home');

// تقارير النظام - حسب الصلاحيات
Route::prefix('reports')->middleware(['auth'])->group(function () {
    Route::get('item-movement', [App\Http\Controllers\ReportController::class, 'itemMovement'])
        ->middleware('permission:view inventory reports')
        ->name('reports.item_movement');
    Route::get('warehouse', [App\Http\Controllers\ReportController::class, 'warehouse'])
        ->middleware('permission:view inventory reports')
        ->name('reports.warehouse');
    Route::get('sales', [App\Http\Controllers\ReportController::class, 'sales'])
        ->middleware('permission:view sales reports')
        ->name('reports.sales');
    Route::get('purchases', [App\Http\Controllers\ReportController::class, 'purchases'])
        ->middleware('permission:view purchase reports')
        ->name('reports.purchases');
    Route::get('inventory', [App\Http\Controllers\ReportController::class, 'inventory'])
        ->middleware('permission:view inventory reports')
        ->name('reports.inventory');
    Route::get('production', [App\Http\Controllers\ReportController::class, 'production'])
        ->middleware('permission:view production reports')
        ->name('reports.production');
    Route::get('financial', [App\Http\Controllers\ReportController::class, 'financial'])
        ->middleware('permission:view financial reports')
        ->name('reports.financial');
    Route::get('treasury', [App\Http\Controllers\ReportController::class, 'treasury'])
        ->middleware('permission:view treasury reports')
        ->name('reports.treasury');
    Route::get('expenses', [App\Http\Controllers\ReportController::class, 'expenses'])
        ->middleware('permission:view expense reports')
        ->name('reports.expenses');
    Route::get('revenues', [App\Http\Controllers\ReportController::class, 'revenues'])
        ->middleware('permission:view revenue reports')
        ->name('reports.revenues');
    Route::get('audit', [App\Http\Controllers\ReportController::class, 'audit'])
        ->middleware('permission:view audit reports')
        ->name('reports.audit');
    Route::get('performance', [App\Http\Controllers\ReportController::class, 'performance'])
        ->middleware('permission:view performance reports')
        ->name('reports.performance');

    // صافي الربح من الـ GL حسب الفترات
    Route::get('net-profit', [ProfitabilityController::class, 'netProfitReport'])
        ->middleware('permission:view financial reports')
        ->name('reports.net_profit');
    Route::get('net-profit/daily', [ProfitabilityController::class, 'netProfitDaily'])
        ->middleware('permission:view financial reports')
        ->name('reports.net_profit.daily');
    Route::get('net-profit/monthly', [ProfitabilityController::class, 'netProfitMonthly'])
        ->middleware('permission:view financial reports')
        ->name('reports.net_profit.monthly');
    Route::get('net-profit/yearly', [ProfitabilityController::class, 'netProfitYearly'])
        ->middleware('permission:view financial reports')
        ->name('reports.net_profit.yearly');
});

// إدارة فواتير الشراء - للمدير
Route::resource('purchase_invoices', App\Http\Controllers\PurchaseInvoiceController::class)
    ->middleware(['auth', 'permission:manage purchasing']);

// إدارة مدفوعات الموردين - للمحاسب والمدير
Route::resource('supplier_payments', App\Http\Controllers\SupplierPaymentController::class)
    ->middleware(['auth', 'permission:manage supplier payments']);

// إدارة حسابات الصندوق والبنك - للمحاسب والمدير
Route::resource('cash_accounts', App\Http\Controllers\CashAccountController::class)
    ->middleware(['auth', 'permission:manage cash accounts']);

// إدارة سندات القبض - للمحاسب والمدير
Route::resource('receipt_vouchers', App\Http\Controllers\ReceiptVoucherController::class)
    ->middleware(['auth', 'permission:manage receipt vouchers']);

// إدارة سندات الصرف - للمحاسب والمدير
Route::resource('payment_vouchers', App\Http\Controllers\PaymentVoucherController::class)
    ->middleware(['auth', 'permission:manage payment vouchers']);

// إدارة تصنيفات الأصول - للمدير
Route::resource('asset_categories', App\Http\Controllers\AssetCategoryController::class)
    ->middleware(['auth', 'permission:manage asset categories']);

// إدارة إهلاك الأصول - للمدير
Route::resource('asset_depreciations', App\Http\Controllers\AssetDepreciationController::class)
    ->middleware(['auth', 'permission:manage asset depreciations']);

// إدارة جرد الأصول - للمدير
Route::resource('asset_inventories', App\Http\Controllers\AssetInventoryController::class)
    ->middleware(['auth', 'permission:manage asset inventories']);

// إدارة أنواع المصروفات - للمحاسب والمدير
Route::resource('expense_types', App\Http\Controllers\ExpenseTypeController::class)
    ->middleware(['auth', 'permission:manage expenses']);

// إدارة أنواع الإيرادات - للمحاسب والمدير
Route::resource('revenue_types', App\Http\Controllers\RevenueTypeController::class)
    ->middleware(['auth', 'permission:manage revenues']);

// إدارة المصروفات - للمحاسب والمدير
Route::resource('expenses', App\Http\Controllers\ExpenseController::class)
    ->middleware(['auth', 'permission:manage expenses']);

// إدارة الإيرادات - للمحاسب والمدير
Route::resource('revenues', App\Http\Controllers\RevenueController::class)
    ->middleware(['auth', 'permission:manage revenues']);

// إدارة الخزنة العامة - للمحاسب والمدير
Route::prefix('treasury')->middleware(['auth', 'permission:manage treasury'])->group(function () {
    Route::get('/', [App\Http\Controllers\TreasuryController::class, 'index'])->name('treasury.index');
    Route::get('/report', [App\Http\Controllers\TreasuryController::class, 'report'])->name('treasury.report');
    Route::post('/receipt', [App\Http\Controllers\TreasuryController::class, 'addReceipt'])->name('treasury.add_receipt');
    Route::post('/payment', [App\Http\Controllers\TreasuryController::class, 'addPayment'])->name('treasury.add_payment');
    Route::get('/{treasury}', [App\Http\Controllers\TreasuryController::class, 'show'])->name('treasury.show');
    Route::get('/{treasury}/edit', [App\Http\Controllers\TreasuryController::class, 'edit'])->name('treasury.edit');
    Route::put('/{treasury}', [App\Http\Controllers\TreasuryController::class, 'update'])->name('treasury.update');
});

// نظام الطباعة - حسب الصلاحيات
Route::prefix('print')->middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return view('prints.index');
    })->middleware('permission:print reports')->name('print.index');
    
    Route::get('/treasury-report', [App\Http\Controllers\PrintController::class, 'treasuryReport'])
        ->middleware('permission:print reports')->name('print.treasury_report');
    
    Route::get('/receipt-voucher/{receiptVoucher}', [App\Http\Controllers\PrintController::class, 'receiptVoucher'])
        ->middleware('permission:print vouchers')->name('print.receipt_voucher');
    
    Route::get('/payment-voucher/{paymentVoucher}', [App\Http\Controllers\PrintController::class, 'paymentVoucher'])
        ->middleware('permission:print vouchers')->name('print.payment_voucher');
    
    Route::get('/purchase-invoice/{purchaseInvoice}', [App\Http\Controllers\PrintController::class, 'purchaseInvoice'])
        ->middleware('permission:print invoices')->name('print.purchase_invoice');
    
    Route::get('/sales-invoice/{salesOrder}', [App\Http\Controllers\PrintController::class, 'salesInvoice'])
        ->middleware('permission:print invoices')->name('print.sales_invoice');
    
    Route::get('/expenses-report', [App\Http\Controllers\PrintController::class, 'expensesReport'])
        ->middleware('permission:print reports')->name('print.expenses_report');
    
    Route::get('/revenues-report', [App\Http\Controllers\PrintController::class, 'revenuesReport'])
        ->middleware('permission:print reports')->name('print.revenues_report');
    
    Route::get('/inventory-report', [App\Http\Controllers\PrintController::class, 'inventoryReport'])
        ->middleware('permission:print reports')->name('print.inventory_report');
    
    Route::get('/sales-report', [App\Http\Controllers\PrintController::class, 'salesReport'])
        ->middleware('permission:print reports')->name('print.sales_report');
    
    Route::get('/supplier-payments-report', [App\Http\Controllers\PrintController::class, 'supplierPaymentsReport'])
        ->middleware('permission:print reports')->name('print.supplier_payments_report');
    
    Route::get('/financial-report', [App\Http\Controllers\PrintController::class, 'financialReport'])
        ->middleware('permission:print reports')->name('print.financial_report');
    
    Route::get('/audit-report', [App\Http\Controllers\PrintController::class, 'auditReport'])
        ->middleware('permission:print reports')->name('print.audit_report');
    
    Route::get('/performance-report', [App\Http\Controllers\PrintController::class, 'performanceReport'])
        ->middleware('permission:print reports')->name('print.performance_report');
    
    Route::get('/purchases-report', [App\Http\Controllers\PrintController::class, 'purchasesReport'])
        ->middleware('permission:print reports')->name('print.purchases_report');
    
    Route::get('/production-report', [App\Http\Controllers\PrintController::class, 'productionReport'])
        ->middleware('permission:print reports')->name('print.production_report');
});

Route::get('customers/{customer}/add-payment', [\App\Http\Controllers\PaymentController::class, 'createForCustomer'])->name('customers.add_payment');
Route::get('suppliers/{supplier}/add-payment', [\App\Http\Controllers\PaymentController::class, 'createForSupplier'])->name('suppliers.add_payment');
Route::post('payments/store', [\App\Http\Controllers\PaymentController::class, 'store'])->name('payments.store');
Route::get('payments/{payment}', [\App\Http\Controllers\PaymentController::class, 'show'])->name('payments.show');
Route::resource('sales_representatives', \App\Http\Controllers\SalesRepresentativeController::class);
Route::get('sales_representatives/{salesRepresentative}/add-transaction', [\App\Http\Controllers\RepresentativeTransactionController::class, 'createForRepresentative'])->name('representative_transactions.create');
Route::post('representative_transactions/store', [\App\Http\Controllers\RepresentativeTransactionController::class, 'store'])->name('representative_transactions.store');

// أوامر الإنتاج - إضافة الراوترز الجديدة
Route::put('production_orders/{productionOrder}/activate', [\App\Http\Controllers\ProductionOrderController::class, 'activate'])->name('production_orders.activate');
Route::put('production_orders/{productionOrder}/start', [\App\Http\Controllers\ProductionOrderController::class, 'startProduction'])->name('production_orders.start');
Route::put('production_orders/{productionOrder}/complete', [\App\Http\Controllers\ProductionOrderController::class, 'completeProduction'])->name('production_orders.complete');

// أوامر الشراء - إضافة الراوترز الجديدة
Route::put('purchase_orders/{purchaseOrder}/confirm', [\App\Http\Controllers\PurchaseOrderController::class, 'confirm'])->name('purchase_orders.confirm');
Route::put('purchase_orders/{purchaseOrder}/receive', [\App\Http\Controllers\PurchaseOrderController::class, 'receive'])->name('purchase_orders.receive');

// مدفوعات الموردين - إضافة الراوترز الجديدة
Route::put('supplier_payments/{supplierPayment}/pay', [\App\Http\Controllers\SupplierPaymentController::class, 'pay'])->name('supplier_payments.pay');
