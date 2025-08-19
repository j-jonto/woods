# المرحلة الأولى: إصلاح ربط الخزنة بالمعاملات المالية

## الإصلاحات المطبقة

### 1. ربط المبيعات النقدية بالخزنة ✅

#### التغييرات:
- **ملف**: `app/Http/Controllers/SalesOrderController.php`
- **إضافة**: دالة `addSalesToTreasury()` لإضافة المبيعات النقدية للخزنة
- **إضافة**: دالة `removeSalesFromTreasury()` لإزالة المبيعات من الخزنة عند الإلغاء
- **إضافة**: ربط تلقائي عند تأكيد المبيعات النقدية
- **إضافة**: ربط تلقائي عند إلغاء المبيعات النقدية

#### الكود المضاف:
```php
private function addSalesToTreasury($salesOrder)
{
    $treasury = Treasury::first();
    if (!$treasury) {
        throw new \Exception('لا توجد خزنة متاحة في النظام');
    }

    try {
        $treasury->addReceipt(
            $salesOrder->total_amount,
            'مبيعات نقدية - طلب رقم: ' . $salesOrder->order_no,
            'sales_order',
            $salesOrder->id
        );
    } catch (\Exception $e) {
        \Log::error('خطأ في إضافة المبيعات للخزنة', [
            'error' => $e->getMessage(),
            'sales_order_id' => $salesOrder->id,
            'amount' => $salesOrder->total_amount
        ]);
        throw new \Exception('فشل في إضافة المبيعات للخزنة: ' . $e->getMessage());
    }
}
```

### 2. ربط المشتريات النقدية بالخزنة ✅

#### التغييرات:
- **ملف**: `app/Http/Controllers/PurchaseOrderController.php`
- **إضافة**: دالة `deductPurchaseFromTreasury()` لخصم المشتريات النقدية من الخزنة
- **تحسين**: ربط تلقائي عند استلام البضاعة بدفع نقدي

#### الكود المضاف:
```php
private function deductPurchaseFromTreasury($purchaseOrder)
{
    $treasury = Treasury::first();
    if (!$treasury) {
        throw new \Exception('لا توجد خزنة متاحة في النظام');
    }

    try {
        $treasury->addPayment(
            $purchaseOrder->total_amount,
            'دفع نقدي للمورد: ' . $purchaseOrder->supplier->name . ' - أمر شراء: ' . $purchaseOrder->order_no,
            'purchase_order',
            $purchaseOrder->id
        );
    } catch (\Exception $e) {
        \Log::error('خطأ في خصم المشتريات من الخزنة', [
            'error' => $e->getMessage(),
            'purchase_order_id' => $purchaseOrder->id,
            'amount' => $purchaseOrder->total_amount
        ]);
        throw new \Exception('فشل في خصم المشتريات من الخزنة: ' . $e->getMessage());
    }
}
```

### 3. ربط سندات القبض والصرف بالخزنة ✅

#### التغييرات:
- **ملف**: `app/Http/Controllers/ReceiptVoucherController.php`
- **ملف**: `app/Http/Controllers/PaymentVoucherController.php`
- **إضافة**: ربط تلقائي لسندات القبض بالخزنة
- **إضافة**: ربط تلقائي لسندات الصرف بالخزنة
- **إضافة**: معاملات قاعدة البيانات لضمان التكامل

#### الكود المضاف:
```php
// في ReceiptVoucherController
private function addReceiptToTreasury($data)
{
    $treasury = Treasury::first();
    if (!$treasury) {
        throw new \Exception('لا توجد خزنة متاحة في النظام');
    }

    try {
        $treasury->addReceipt(
            $data['amount'],
            'سند قبض - ' . ($data['source'] ?? 'غير محدد'),
            'receipt_voucher',
            null
        );
    } catch (\Exception $e) {
        \Log::error('خطأ في إضافة سند القبض للخزنة', [
            'error' => $e->getMessage(),
            'amount' => $data['amount']
        ]);
        throw new \Exception('فشل في إضافة سند القبض للخزنة: ' . $e->getMessage());
    }
}

// في PaymentVoucherController
private function deductPaymentFromTreasury($data)
{
    $treasury = Treasury::first();
    if (!$treasury) {
        throw new \Exception('لا توجد خزنة متاحة في النظام');
    }

    try {
        $treasury->addPayment(
            $data['amount'],
            'سند صرف - ' . ($data['destination'] ?? 'غير محدد'),
            'payment_voucher',
            null
        );
    } catch (\Exception $e) {
        \Log::error('خطأ في خصم سند الصرف من الخزنة', [
            'error' => $e->getMessage(),
            'amount' => $data['amount']
        ]);
        throw new \Exception('فشل في خصم سند الصرف من الخزنة: ' . $e->getMessage());
    }
}
```

### 4. حساب العمولات تلقائياً ✅

#### التغييرات:
- **ملف**: `app/Http/Controllers/SalesOrderController.php`
- **إضافة**: دالة `calculateCommission()` لحساب العمولات تلقائياً
- **إضافة**: ربط العمولات بالخزنة
- **إضافة**: تسجيل معاملات العمولات للمندوبين

#### الكود المضاف:
```php
private function calculateCommission($salesOrder)
{
    try {
        $representative = $salesOrder->representative;
        $commission = $salesOrder->total_amount * ($representative->commission_rate / 100);
        
        RepresentativeTransaction::create([
            'representative_id' => $representative->id,
            'type' => 'commission',
            'amount' => $commission,
            'transaction_date' => now(),
            'reference' => 'عمولة مبيعات - طلب رقم: ' . $salesOrder->order_no,
            'notes' => 'عمولة مبيعات بنسبة ' . $representative->commission_rate . '%',
            'created_by' => auth()->id(),
        ]);

        // خصم العمولة من الخزنة
        $treasury = Treasury::first();
        if ($treasury) {
            $treasury->addPayment(
                $commission,
                'عمولة مندوب: ' . $representative->name . ' - طلب رقم: ' . $salesOrder->order_no,
                'representative_commission',
                $representative->id
            );
        }

    } catch (\Exception $e) {
        \Log::error('خطأ في حساب العمولة', [
            'error' => $e->getMessage(),
            'sales_order_id' => $salesOrder->id,
            'representative_id' => $salesOrder->representative_id
        ]);
        throw new \Exception('فشل في حساب العمولة: ' . $e->getMessage());
    }
}
```

### 5. تحسين عرض رصيد الخزنة ✅

#### التغييرات:
- **ملف**: `app/Http/Controllers/DashboardController.php`
- **ملف**: `app/Http/Controllers/TreasuryController.php`
- **إضافة**: عرض رصيد الخزنة في الداشبورد
- **إضافة**: دوال API لفحص رصيد الخزنة
- **إضافة**: تقارير مفصلة للخزنة

## النتائج المتوقعة

### قبل الإصلاح:
- ❌ رصيد الخزنة لا يعكس الواقع
- ❌ المبيعات النقدية لا تؤثر على الخزنة
- ❌ المشتريات النقدية لا تؤثر على الخزنة
- ❌ سندات القبض والصرف لا تؤثر على الخزنة
- ❌ العمولات لا تحسب تلقائياً

### بعد الإصلاح:
- ✅ رصيد الخزنة يعكس الواقع الفعلي
- ✅ المبيعات النقدية تضيف للخزنة تلقائياً
- ✅ المشتريات النقدية تخصم من الخزنة تلقائياً
- ✅ سندات القبض والصرف تؤثر على الخزنة
- ✅ العمولات تحسب وتخصم تلقائياً

## الاختبار المطلوب

### 1. اختبار المبيعات النقدية:
1. إنشاء طلب بيع بنوع دفع "نقدي"
2. تأكيد الطلب
3. التحقق من إضافة المبلغ للخزنة
4. إلغاء الطلب
5. التحقق من خصم المبلغ من الخزنة

### 2. اختبار المشتريات النقدية:
1. إنشاء أمر شراء بنوع دفع "نقدي"
2. استلام البضاعة
3. التحقق من خصم المبلغ من الخزنة

### 3. اختبار سندات القبض والصرف:
1. إنشاء سند قبض
2. التحقق من إضافة المبلغ للخزنة
3. إنشاء سند صرف
4. التحقق من خصم المبلغ من الخزنة

### 4. اختبار العمولات:
1. إنشاء طلب بيع مع مندوب
2. تأكيد الطلب
3. التحقق من حساب العمولة
4. التحقق من خصم العمولة من الخزنة

## الخطوات التالية

### المرحلة الثانية: إصلاح الحسابات المدينة والدائنة
1. إضافة حقول الأرصدة للعملاء والموردين
2. ربط المبيعات الآجلة بحسابات العملاء
3. ربط المشتريات الآجلة بحسابات الموردين

### المرحلة الثالثة: تطبيق القيد المزدوج
1. إنشاء قيود محاسبية تلقائياً
2. ربط المعاملات بالحسابات
3. إعداد نظام إغلاق الحسابات

## ملاحظات مهمة

1. **النسخ الاحتياطي**: تم الاحتفاظ بنسخ احتياطية قبل التطبيق
2. **التسجيل**: جميع الأخطاء مسجلة في ملفات السجل
3. **الاختبار**: يجب اختبار جميع الوظائف قبل الانتقال للمرحلة التالية
4. **التوثيق**: جميع التغييرات موثقة في هذا الملف 