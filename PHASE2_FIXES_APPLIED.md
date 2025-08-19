# المرحلة الثانية: إصلاح الحسابات المدينة والدائنة

## الإصلاحات المطبقة

### 1. إضافة حقول الأرصدة للعملاء ✅

#### التغييرات:
- **ملف**: `database/migrations/2025_08_01_160000_add_balance_fields_to_customers_table.php`
- **إضافة**: حقول الأرصدة للعملاء
  - `opening_balance` - الرصيد الافتتاحي
  - `current_balance` - الرصيد الحالي
  - `total_receivables` - إجمالي المستحقات
  - `total_payments` - إجمالي المدفوعات
  - `last_transaction_date` - تاريخ آخر معاملة

#### الكود المضاف:
```php
Schema::table('customers', function (Blueprint $table) {
    $table->decimal('opening_balance', 15, 2)->default(0)->after('credit_limit');
    $table->decimal('current_balance', 15, 2)->default(0)->after('opening_balance');
    $table->decimal('total_receivables', 15, 2)->default(0)->after('current_balance');
    $table->decimal('total_payments', 15, 2)->default(0)->after('total_receivables');
    $table->date('last_transaction_date')->nullable()->after('total_payments');
});
```

### 2. إضافة حقول الأرصدة للموردين ✅

#### التغييرات:
- **ملف**: `database/migrations/2025_08_01_160001_add_balance_fields_to_suppliers_table.php`
- **إضافة**: حقول الأرصدة للموردين
  - `opening_balance` - الرصيد الافتتاحي
  - `current_balance` - الرصيد الحالي
  - `total_payables` - إجمالي المستحقات عليه
  - `total_payments` - إجمالي المدفوعات
  - `last_transaction_date` - تاريخ آخر معاملة

#### الكود المضاف:
```php
Schema::table('suppliers', function (Blueprint $table) {
    $table->decimal('opening_balance', 15, 2)->default(0)->after('phone');
    $table->decimal('current_balance', 15, 2)->default(0)->after('opening_balance');
    $table->decimal('total_payables', 15, 2)->default(0)->after('current_balance');
    $table->decimal('total_payments', 15, 2)->default(0)->after('total_payables');
    $table->date('last_transaction_date')->nullable()->after('total_payments');
});
```

### 3. تحديث نموذج العميل ✅

#### التغييرات:
- **ملف**: `app/Models/Customer.php`
- **إضافة**: حقول الأرصدة للـ fillable
- **إضافة**: cast types للحقول الجديدة
- **إضافة**: دوال إدارة الأرصدة

#### الكود المضاف:
```php
// دوال إدارة الأرصدة
public function addReceivable($amount, $description = null, $referenceType = null, $referenceId = null)
{
    $this->increment('current_balance', $amount);
    $this->increment('total_receivables', $amount);
    $this->update(['last_transaction_date' => now()]);

    // تسجيل المعاملة في جدول المدفوعات
    Payment::create([
        'customer_id' => $this->id,
        'amount' => $amount,
        'payment_date' => now(),
        'payment_type' => 'credit',
        'reference_type' => $referenceType,
        'reference_id' => $referenceId,
        'notes' => $description ?? 'مبيعات آجلة',
        'created_by' => auth()->id(),
    ]);
}

public function addPayment($amount, $description = null, $referenceType = null, $referenceId = null)
{
    $this->decrement('current_balance', $amount);
    $this->increment('total_payments', $amount);
    $this->update(['last_transaction_date' => now()]);

    // تسجيل المعاملة في جدول المدفوعات
    Payment::create([
        'customer_id' => $this->id,
        'amount' => $amount,
        'payment_date' => now(),
        'payment_type' => 'payment',
        'reference_type' => $referenceType,
        'reference_id' => $referenceId,
        'notes' => $description ?? 'دفع من العميل',
        'created_by' => auth()->id(),
    ]);
}

public function getSummary()
{
    return [
        'opening_balance' => $this->opening_balance,
        'current_balance' => $this->current_balance,
        'total_receivables' => $this->total_receivables,
        'total_payments' => $this->total_payments,
        'credit_limit' => $this->credit_limit,
        'available_credit' => $this->credit_limit - $this->current_balance,
        'is_over_limit' => $this->current_balance > $this->credit_limit,
    ];
}
```

### 4. تحديث نموذج المورد ✅

#### التغييرات:
- **ملف**: `app/Models/Supplier.php`
- **إضافة**: حقول الأرصدة للـ fillable
- **إضافة**: cast types للحقول الجديدة
- **إضافة**: دوال إدارة الأرصدة

#### الكود المضاف:
```php
// دوال إدارة الأرصدة
public function addPayable($amount, $description = null, $referenceType = null, $referenceId = null)
{
    $this->increment('current_balance', $amount);
    $this->increment('total_payables', $amount);
    $this->update(['last_transaction_date' => now()]);

    // تسجيل المعاملة في جدول مدفوعات الموردين
    SupplierPayment::create([
        'supplier_id' => $this->id,
        'amount' => $amount,
        'payment_date' => now(),
        'reference_type' => $referenceType,
        'reference_id' => $referenceId,
        'status' => 'pending',
        'method' => 'credit',
        'created_by' => auth()->id(),
    ]);
}

public function addPayment($amount, $description = null, $referenceType = null, $referenceId = null)
{
    $this->decrement('current_balance', $amount);
    $this->increment('total_payments', $amount);
    $this->update(['last_transaction_date' => now()]);

    // تسجيل المعاملة في جدول مدفوعات الموردين
    SupplierPayment::create([
        'supplier_id' => $this->id,
        'amount' => $amount,
        'payment_date' => now(),
        'reference_type' => $referenceType,
        'reference_id' => $referenceId,
        'status' => 'completed',
        'method' => 'payment',
        'created_by' => auth()->id(),
    ]);
}

public function getSummary()
{
    return [
        'opening_balance' => $this->opening_balance,
        'current_balance' => $this->current_balance,
        'total_payables' => $this->total_payables,
        'total_payments' => $this->total_payments,
        'last_transaction_date' => $this->last_transaction_date,
    ];
}
```

### 5. ربط المبيعات الآجلة بحسابات العملاء ✅

#### التغييرات:
- **ملف**: `app/Http/Controllers/SalesOrderController.php`
- **إضافة**: دالة `addSalesToCustomerAccount()`
- **إضافة**: دالة `removeSalesFromCustomerAccount()`
- **إضافة**: ربط تلقائي للمبيعات الآجلة بحسابات العملاء

#### الكود المضاف:
```php
private function addSalesToCustomerAccount($salesOrder)
{
    try {
        $customer = $salesOrder->customer;
        if (!$customer) {
            throw new \Exception('العميل غير موجود');
        }

        // التحقق من الحد الائتماني
        $summary = $customer->getSummary();
        if ($summary['is_over_limit']) {
            throw new \Exception('العميل تجاوز الحد الائتماني المسموح');
        }

        $customer->addReceivable(
            $salesOrder->total_amount,
            'مبيعات آجلة - طلب رقم: ' . $salesOrder->order_no,
            'sales_order',
            $salesOrder->id
        );

    } catch (\Exception $e) {
        \Log::error('خطأ في إضافة المبيعات لحساب العميل', [
            'error' => $e->getMessage(),
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $salesOrder->customer_id
        ]);
        throw new \Exception('فشل في إضافة المبيعات لحساب العميل: ' . $e->getMessage());
    }
}

private function removeSalesFromCustomerAccount($salesOrder)
{
    try {
        $customer = $salesOrder->customer;
        if (!$customer) {
            throw new \Exception('العميل غير موجود');
        }

        $customer->addPayment(
            $salesOrder->total_amount,
            'إلغاء مبيعات آجلة - طلب رقم: ' . $salesOrder->order_no,
            'sales_order',
            $salesOrder->id
        );

    } catch (\Exception $e) {
        \Log::error('خطأ في إزالة المبيعات من حساب العميل', [
            'error' => $e->getMessage(),
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $salesOrder->customer_id
        ]);
        throw new \Exception('فشل في إزالة المبيعات من حساب العميل: ' . $e->getMessage());
    }
}
```

### 6. ربط المشتريات الآجلة بحسابات الموردين ✅

#### التغييرات:
- **ملف**: `app/Http/Controllers/PurchaseOrderController.php`
- **إضافة**: دالة `addPurchaseToSupplierAccount()`
- **تحسين**: ربط تلقائي للمشتريات الآجلة بحسابات الموردين

#### الكود المضاف:
```php
private function addPurchaseToSupplierAccount($purchaseOrder)
{
    try {
        $supplier = $purchaseOrder->supplier;
        if (!$supplier) {
            throw new \Exception('المورد غير موجود');
        }

        $supplier->addPayable(
            $purchaseOrder->total_amount,
            'مشتريات آجلة - أمر شراء: ' . $purchaseOrder->order_no,
            'purchase_order',
            $purchaseOrder->id
        );

    } catch (\Exception $e) {
        \Log::error('خطأ في إضافة المشتريات لحساب المورد', [
            'error' => $e->getMessage(),
            'purchase_order_id' => $purchaseOrder->id,
            'supplier_id' => $purchaseOrder->supplier_id
        ]);
        throw new \Exception('فشل في إضافة المشتريات لحساب المورد: ' . $e->getMessage());
    }
}
```

### 7. إنشاء controller لإدارة مدفوعات العملاء ✅

#### التغييرات:
- **ملف**: `app/Http/Controllers/CustomerPaymentController.php`
- **إضافة**: إدارة كاملة لمدفوعات العملاء
- **إضافة**: ربط مدفوعات العملاء بالخزنة
- **إضافة**: دوال API لفحص أرصدة العملاء

#### الميزات:
- تسجيل مدفوعات العملاء
- التحقق من الرصيد قبل الدفع
- ربط المدفوعات النقدية بالخزنة
- عرض تفاصيل مدفوعات العميل
- فحص رصيد العميل عبر API

### 8. إنشاء controller لإدارة مدفوعات الموردين ✅

#### التغييرات:
- **ملف**: `app/Http/Controllers/SupplierPaymentController.php`
- **إضافة**: إدارة كاملة لمدفوعات الموردين
- **إضافة**: ربط مدفوعات الموردين بالخزنة
- **إضافة**: إدارة المدفوعات المعلقة

#### الميزات:
- تسجيل مدفوعات الموردين
- التحقق من الرصيد قبل الدفع
- ربط المدفوعات النقدية بالخزنة
- عرض تفاصيل مدفوعات المورد
- إدارة المدفوعات المعلقة
- فحص رصيد المورد عبر API

## النتائج المتوقعة

### قبل الإصلاح:
- ❌ لا توجد أرصدة للعملاء والموردين
- ❌ المبيعات الآجلة لا تؤثر على حسابات العملاء
- ❌ المشتريات الآجلة لا تؤثر على حسابات الموردين
- ❌ لا توجد إدارة لمدفوعات العملاء والموردين
- ❌ لا يوجد ربط بين المدفوعات والخزنة

### بعد الإصلاح:
- ✅ أرصدة دقيقة للعملاء والموردين
- ✅ المبيعات الآجلة تضيف لحسابات العملاء تلقائياً
- ✅ المشتريات الآجلة تضيف لحسابات الموردين تلقائياً
- ✅ إدارة كاملة لمدفوعات العملاء والموردين
- ✅ ربط تلقائي بين المدفوعات والخزنة
- ✅ التحقق من الحدود الائتمانية
- ✅ تقارير مفصلة للأرصدة

## الاختبار المطلوب

### 1. اختبار المبيعات الآجلة:
1. إنشاء طلب بيع بنوع دفع "آجل"
2. تأكيد الطلب
3. التحقق من إضافة المبلغ لحساب العميل
4. إلغاء الطلب
5. التحقق من خصم المبلغ من حساب العميل

### 2. اختبار المشتريات الآجلة:
1. إنشاء أمر شراء بنوع دفع "آجل"
2. استلام البضاعة
3. التحقق من إضافة المبلغ لحساب المورد

### 3. اختبار مدفوعات العملاء:
1. تسجيل دفعة من عميل
2. التحقق من خصم المبلغ من حساب العميل
3. التحقق من إضافة المبلغ للخزنة (إذا كان نقدي)

### 4. اختبار مدفوعات الموردين:
1. تسجيل دفعة لمورد
2. التحقق من خصم المبلغ من حساب المورد
3. التحقق من خصم المبلغ من الخزنة (إذا كان نقدي)

### 5. اختبار الحدود الائتمانية:
1. إنشاء طلب بيع لعميل تجاوز حد الائتمان
2. التحقق من رفض الطلب

## الخطوات التالية

### المرحلة الثالثة: تطبيق القيد المزدوج
1. إنشاء قيود محاسبية تلقائياً
2. ربط المعاملات بالحسابات
3. إعداد نظام إغلاق الحسابات

### المرحلة الرابعة: تحسين التقارير
1. تقارير الأرصدة المدينة والدائنة
2. تقارير الأعمار
3. تقارير الربحية

## ملاحظات مهمة

1. **النسخ الاحتياطي**: تم الاحتفاظ بنسخ احتياطية قبل التطبيق
2. **التسجيل**: جميع الأخطاء مسجلة في ملفات السجل
3. **الاختبار**: يجب اختبار جميع الوظائف قبل الانتقال للمرحلة التالية
4. **التوثيق**: جميع التغييرات موثقة في هذا الملف
5. **الحدود الائتمانية**: تم تطبيق التحقق من الحدود الائتمانية للعملاء
6. **الربط التلقائي**: جميع المعاملات مرتبطة تلقائياً بالخزنة والحسابات 