<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\ProductionOrder;
use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use App\Models\AuditAlert;
use App\Models\Treasury;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * فحص جميع التنبيهات
     */
    public static function checkAllAlerts()
    {
        self::checkLowStockAlerts();
        self::checkOverdueCustomerAlerts();
        self::checkOverdueSupplierAlerts();
        self::checkProductionAlerts();
        self::checkTreasuryAlerts();
        self::checkSalesAlerts();
        self::checkPurchaseAlerts();
        
        Log::info('تم فحص جميع التنبيهات');
    }

    /**
     * فحص تنبيهات المخزون المنخفض
     */
    public static function checkLowStockAlerts()
    {
        $lowStockItems = Item::where('minimum_stock', '>', 0)
            ->get()
            ->filter(function ($item) {
                return $item->getCurrentStock() <= $item->minimum_stock;
            });

        foreach ($lowStockItems as $item) {
            $existingAlert = AuditAlert::where('alert_type', 'inventory')
                ->where('title', 'مخزون منخفض')
                ->where('description', 'like', "%{$item->name}%")
                ->where('is_resolved', false)
                ->first();

            if (!$existingAlert) {
                AuditAlert::create([
                    'alert_type' => 'inventory',
                    'severity' => 'medium',
                    'title' => 'مخزون منخفض',
                    'description' => "المنتج {$item->name} وصل للمستوى الأدنى للمخزون. الكمية الحالية: {$item->getCurrentStock()}",
                    'alert_data' => json_encode([
                        'item_id' => $item->id,
                        'item_name' => $item->name,
                        'current_stock' => $item->getCurrentStock(),
                        'minimum_stock' => $item->minimum_stock,
                    ]),
                ]);
            }
        }
    }

    /**
     * فحص تنبيهات العملاء المتجاوزين للحد الائتماني
     */
    public static function checkOverdueCustomerAlerts()
    {
        $overdueCustomers = Customer::where('current_balance', '>', 0)
            ->where('credit_limit', '>', 0)
            ->get()
            ->filter(function ($customer) {
                return $customer->current_balance > $customer->credit_limit;
            });

        foreach ($overdueCustomers as $customer) {
            $existingAlert = AuditAlert::where('alert_type', 'financial')
                ->where('title', 'عميل متجاوز للحد الائتماني')
                ->where('description', 'like', "%{$customer->name}%")
                ->where('is_resolved', false)
                ->first();

            if (!$existingAlert) {
                AuditAlert::create([
                    'alert_type' => 'financial',
                    'severity' => 'high',
                    'title' => 'عميل متجاوز للحد الائتماني',
                    'description' => "العميل {$customer->name} تجاوز الحد الائتماني. الرصيد الحالي: {$customer->current_balance}، الحد الائتماني: {$customer->credit_limit}",
                    'alert_data' => json_encode([
                        'customer_id' => $customer->id,
                        'customer_name' => $customer->name,
                        'current_balance' => $customer->current_balance,
                        'credit_limit' => $customer->credit_limit,
                    ]),
                ]);
            }
        }
    }

    /**
     * فحص تنبيهات الموردين المتأخرين
     */
    public static function checkOverdueSupplierAlerts()
    {
        $overdueSuppliers = Supplier::where('current_balance', '>', 10000)
            ->get();

        foreach ($overdueSuppliers as $supplier) {
            $existingAlert = AuditAlert::where('alert_type', 'financial')
                ->where('title', 'مدفوعات للموردين')
                ->where('description', 'like', "%{$supplier->name}%")
                ->where('is_resolved', false)
                ->first();

            if (!$existingAlert) {
                AuditAlert::create([
                    'alert_type' => 'financial',
                    'severity' => 'medium',
                    'title' => 'مدفوعات للموردين',
                    'description' => "المورد {$supplier->name} يحتاج إلى دفع. المبلغ المستحق: {$supplier->current_balance}",
                    'alert_data' => json_encode([
                        'supplier_id' => $supplier->id,
                        'supplier_name' => $supplier->name,
                        'current_balance' => $supplier->current_balance,
                    ]),
                ]);
            }
        }
    }

    /**
     * فحص تنبيهات الإنتاج
     */
    public static function checkProductionAlerts()
    {
        // فحص أوامر الإنتاج المتأخرة
        $overdueProduction = ProductionOrder::where('status', 'in_progress')
            ->where('due_date', '<', Carbon::now())
            ->get();

        foreach ($overdueProduction as $order) {
            $existingAlert = AuditAlert::where('alert_type', 'operational')
                ->where('title', 'أمر إنتاج متأخر')
                ->where('description', 'like', "%{$order->order_no}%")
                ->where('is_resolved', false)
                ->first();

            if (!$existingAlert) {
                AuditAlert::create([
                    'alert_type' => 'operational',
                    'severity' => 'medium',
                    'title' => 'أمر إنتاج متأخر',
                    'description' => "أمر الإنتاج {$order->order_no} متأخر. تاريخ الاستحقاق: {$order->due_date}",
                    'alert_data' => json_encode([
                        'production_order_id' => $order->id,
                        'order_no' => $order->order_no,
                        'due_date' => $order->due_date,
                    ]),
                ]);
            }
        }

        // فحص أوامر الإنتاج المعلقة
        $pendingProduction = ProductionOrder::where('status', 'released')
            ->where('created_at', '<', Carbon::now()->subDays(3))
            ->get();

        foreach ($pendingProduction as $order) {
            $existingAlert = AuditAlert::where('alert_type', 'operational')
                ->where('title', 'أمر إنتاج معلق')
                ->where('description', 'like', "%{$order->order_no}%")
                ->where('is_resolved', false)
                ->first();

            if (!$existingAlert) {
                AuditAlert::create([
                    'alert_type' => 'operational',
                    'severity' => 'low',
                    'title' => 'أمر إنتاج معلق',
                    'description' => "أمر الإنتاج {$order->order_no} معلق منذ أكثر من 3 أيام",
                    'alert_data' => json_encode([
                        'production_order_id' => $order->id,
                        'order_no' => $order->order_no,
                        'created_at' => $order->created_at,
                    ]),
                ]);
            }
        }
    }

    /**
     * فحص تنبيهات الخزنة
     */
    public static function checkTreasuryAlerts()
    {
        $treasury = Treasury::first();
        
        if ($treasury) {
            // فحص الرصيد المنخفض
            if ($treasury->current_balance < 10000) {
                $existingAlert = AuditAlert::where('alert_type', 'financial')
                    ->where('title', 'رصيد خزنة منخفض')
                    ->where('is_resolved', false)
                    ->first();

                if (!$existingAlert) {
                    AuditAlert::create([
                        'alert_type' => 'financial',
                        'severity' => 'high',
                        'title' => 'رصيد خزنة منخفض',
                        'description' => "رصيد الخزنة منخفض: {$treasury->current_balance} ر.س",
                        'alert_data' => json_encode([
                            'treasury_id' => $treasury->id,
                            'current_balance' => $treasury->current_balance,
                        ]),
                    ]);
                }
            }

            // فحص الرصيد المرتفع (قد يشير إلى عدم استثمار الأموال)
            if ($treasury->current_balance > 100000) {
                $existingAlert = AuditAlert::where('alert_type', 'financial')
                    ->where('title', 'رصيد خزنة مرتفع')
                    ->where('is_resolved', false)
                    ->first();

                if (!$existingAlert) {
                    AuditAlert::create([
                        'alert_type' => 'financial',
                        'severity' => 'low',
                        'title' => 'رصيد خزنة مرتفع',
                        'description' => "رصيد الخزنة مرتفع: {$treasury->current_balance} ر.س - قد تحتاج إلى استثمار الأموال",
                        'alert_data' => json_encode([
                            'treasury_id' => $treasury->id,
                            'current_balance' => $treasury->current_balance,
                        ]),
                    ]);
                }
            }
        }
    }

    /**
     * فحص تنبيهات المبيعات
     */
    public static function checkSalesAlerts()
    {
        // فحص المبيعات الكبيرة
        $largeSales = SalesOrder::where('status', 'invoiced')
            ->where('total_amount', '>', 50000)
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->get();

        foreach ($largeSales as $sale) {
            $existingAlert = AuditAlert::where('alert_type', 'financial')
                ->where('title', 'مبيعات كبيرة')
                ->where('description', 'like', "%{$sale->order_no}%")
                ->where('is_resolved', false)
                ->first();

            if (!$existingAlert) {
                AuditAlert::create([
                    'alert_type' => 'financial',
                    'severity' => 'medium',
                    'title' => 'مبيعات كبيرة',
                    'description' => "مبيعات كبيرة تمت: {$sale->order_no} - المبلغ: {$sale->total_amount} ر.س",
                    'alert_data' => json_encode([
                        'sales_order_id' => $sale->id,
                        'order_no' => $sale->order_no,
                        'total_amount' => $sale->total_amount,
                        'customer_name' => $sale->customer ? $sale->customer->name : 'غير محدد',
                    ]),
                ]);
            }
        }

        // فحص انخفاض المبيعات
        $todaySales = SalesOrder::where('status', 'invoiced')
            ->whereDate('order_date', Carbon::today())
            ->sum('total_amount');

        $yesterdaySales = SalesOrder::where('status', 'invoiced')
            ->whereDate('order_date', Carbon::yesterday())
            ->sum('total_amount');

        if ($yesterdaySales > 0 && $todaySales < ($yesterdaySales * 0.5)) {
            $existingAlert = AuditAlert::where('alert_type', 'operational')
                ->where('title', 'انخفاض في المبيعات')
                ->where('is_resolved', false)
                ->first();

            if (!$existingAlert) {
                AuditAlert::create([
                    'alert_type' => 'operational',
                    'severity' => 'medium',
                    'title' => 'انخفاض في المبيعات',
                    'description' => "انخفاض ملحوظ في المبيعات اليومية. اليوم: {$todaySales} ر.س، أمس: {$yesterdaySales} ر.س",
                    'alert_data' => json_encode([
                        'today_sales' => $todaySales,
                        'yesterday_sales' => $yesterdaySales,
                        'decrease_percentage' => (($yesterdaySales - $todaySales) / $yesterdaySales) * 100,
                    ]),
                ]);
            }
        }
    }

    /**
     * فحص تنبيهات المشتريات
     */
    public static function checkPurchaseAlerts()
    {
        // فحص المشتريات الكبيرة
        $largePurchases = PurchaseOrder::where('status', 'received')
            ->where('total_amount', '>', 30000)
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->get();

        foreach ($largePurchases as $purchase) {
            $existingAlert = AuditAlert::where('alert_type', 'financial')
                ->where('title', 'مشتريات كبيرة')
                ->where('description', 'like', "%{$purchase->order_no}%")
                ->where('is_resolved', false)
                ->first();

            if (!$existingAlert) {
                AuditAlert::create([
                    'alert_type' => 'financial',
                    'severity' => 'medium',
                    'title' => 'مشتريات كبيرة',
                    'description' => "مشتريات كبيرة تمت: {$purchase->order_no} - المبلغ: {$purchase->total_amount} ر.س",
                    'alert_data' => json_encode([
                        'purchase_order_id' => $purchase->id,
                        'order_no' => $purchase->order_no,
                        'total_amount' => $purchase->total_amount,
                        'supplier_name' => $purchase->supplier ? $purchase->supplier->name : 'غير محدد',
                    ]),
                ]);
            }
        }
    }

    /**
     * حل التنبيهات المحلولة
     */
    public static function resolveAlerts()
    {
        // حل تنبيهات المخزون المنخفض إذا تم إعادة التخزين
        $lowStockAlerts = AuditAlert::where('alert_type', 'inventory')
            ->where('title', 'مخزون منخفض')
            ->where('is_resolved', false)
            ->get();

        foreach ($lowStockAlerts as $alert) {
            $alertData = json_decode($alert->alert_data, true);
            $item = Item::find($alertData['item_id']);
            
            if ($item && $item->getCurrentStock() > $item->minimum_stock) {
                $alert->update([
                    'is_resolved' => true,
                    'resolved_at' => Carbon::now(),
                    'resolution_notes' => 'تم إعادة التخزين',
                ]);
            }
        }

        // حل تنبيهات العملاء المتجاوزين إذا تم السداد
        $overdueCustomerAlerts = AuditAlert::where('alert_type', 'financial')
            ->where('title', 'عميل متجاوز للحد الائتماني')
            ->where('is_resolved', false)
            ->get();

        foreach ($overdueCustomerAlerts as $alert) {
            $alertData = json_decode($alert->alert_data, true);
            $customer = Customer::find($alertData['customer_id']);
            
            if ($customer && $customer->current_balance <= $customer->credit_limit) {
                $alert->update([
                    'is_resolved' => true,
                    'resolved_at' => Carbon::now(),
                    'resolution_notes' => 'تم السداد',
                ]);
            }
        }
    }

    /**
     * إرسال تنبيه فوري
     */
    public static function sendImmediateAlert($type, $severity, $title, $description, $data = null)
    {
        AuditAlert::create([
            'alert_type' => $type,
            'severity' => $severity,
            'title' => $title,
            'description' => $description,
            'alert_data' => $data ? json_encode($data) : null,
        ]);

        Log::info("تنبيه فوري: {$title} - {$description}");
    }

    /**
     * الحصول على إحصائيات التنبيهات
     */
    public static function getAlertStatistics()
    {
        $totalAlerts = AuditAlert::count();
        $unresolvedAlerts = AuditAlert::where('is_resolved', false)->count();
        $criticalAlerts = AuditAlert::where('severity', 'critical')->where('is_resolved', false)->count();
        $highAlerts = AuditAlert::where('severity', 'high')->where('is_resolved', false)->count();
        $mediumAlerts = AuditAlert::where('severity', 'medium')->where('is_resolved', false)->count();
        $lowAlerts = AuditAlert::where('severity', 'low')->where('is_resolved', false)->count();

        return [
            'total_alerts' => $totalAlerts,
            'unresolved_alerts' => $unresolvedAlerts,
            'critical_alerts' => $criticalAlerts,
            'high_alerts' => $highAlerts,
            'medium_alerts' => $mediumAlerts,
            'low_alerts' => $lowAlerts,
        ];
    }
} 