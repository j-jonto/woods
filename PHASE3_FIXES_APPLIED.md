# المرحلة الثالثة: تطبيق القيد المزدوج

## الإصلاحات المطبقة

### 1. إضافة حقول القيد المزدوج لجدول القيود المحاسبية ✅

#### التغييرات:
- **ملف**: `database/migrations/2025_08_01_170000_add_double_entry_fields_to_journal_entries_table.php`
- **إضافة**: حقول القيد المزدوج
  - `entry_type` - نوع القيد (manual, auto, adjustment)
  - `reference_type` - نوع المرجع
  - `reference_id` - معرف المرجع
  - `is_posted` - هل تم ترحيل القيد
  - `posted_at` - تاريخ الترحيل
  - `posted_by` - من قام بالترحيل

#### الكود المضاف:
```php
Schema::table('journal_entries', function (Blueprint $table) {
    $table->string('entry_type')->default('manual')->after('description');
    $table->string('reference_type')->nullable()->after('entry_type');
    $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
    $table->boolean('is_posted')->default(false)->after('reference_id');
    $table->timestamp('posted_at')->nullable()->after('is_posted');
    $table->unsignedBigInteger('posted_by')->nullable()->after('posted_at');
    $table->foreign('posted_by')->references('id')->on('users')->onDelete('set null');
});
```

### 2. إضافة حقول القيد المزدوج لجدول عناصر القيود ✅

#### التغييرات:
- **ملف**: `database/migrations/2025_08_01_170001_add_double_entry_fields_to_journal_entry_items_table.php`
- **إضافة**: حقول عناصر القيد
  - `entry_type` - نوع القيد (debit, credit)
  - `description` - وصف العنصر
  - `reference_type` - نوع المرجع
  - `reference_id` - معرف المرجع

#### الكود المضاف:
```php
Schema::table('journal_entry_items', function (Blueprint $table) {
    $table->string('entry_type')->default('debit')->after('amount');
    $table->text('description')->nullable()->after('entry_type');
    $table->string('reference_type')->nullable()->after('description');
    $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
});
```

### 3. تحديث نموذج JournalEntry ✅

#### التغييرات:
- **ملف**: `app/Models/JournalEntry.php`
- **إضافة**: حقول القيد المزدوج للـ fillable
- **إضافة**: cast types للحقول الجديدة
- **إضافة**: دوال إنشاء القيود التلقائية

#### الكود المضاف:
```php
// دوال إنشاء القيود التلقائية
public static function createSalesEntry($salesOrder)
{
    $entry = self::create([
        'entry_date' => $salesOrder->order_date,
        'reference_no' => 'JE-SALES-' . $salesOrder->order_no,
        'description' => 'مبيعات - طلب رقم: ' . $salesOrder->order_no,
        'entry_type' => 'auto',
        'reference_type' => 'sales_order',
        'reference_id' => $salesOrder->id,
        'status' => 'draft',
        'created_by' => auth()->id(),
    ]);

    // حساب تكلفة البضاعة المباعة
    $cogs = 0;
    foreach ($salesOrder->items as $item) {
        $cogs += ($item->item->standard_cost ?? 0) * $item->quantity;
    }

    // إضافة عناصر القيد
    if ($salesOrder->payment_type == 'cash') {
        // نقدي: من ح/ النقدية إلى ح/ المبيعات
        $entry->items()->create([
            'account_id' => self::getCashAccountId(),
            'entry_type' => 'debit',
            'amount' => $salesOrder->total_amount,
            'description' => 'نقدية',
        ]);

        $entry->items()->create([
            'account_id' => self::getSalesAccountId(),
            'entry_type' => 'credit',
            'amount' => $salesOrder->total_amount,
            'description' => 'مبيعات',
        ]);
    } else {
        // آجل: من ح/ العملاء إلى ح/ المبيعات
        $entry->items()->create([
            'account_id' => self::getCustomerAccountId($salesOrder->customer_id),
            'entry_type' => 'debit',
            'amount' => $salesOrder->total_amount,
            'description' => 'عملاء',
        ]);

        $entry->items()->create([
            'account_id' => self::getSalesAccountId(),
            'entry_type' => 'credit',
            'amount' => $salesOrder->total_amount,
            'description' => 'مبيعات',
        ]);
    }

    // قيد تكلفة البضاعة المباعة
    if ($cogs > 0) {
        $entry->items()->create([
            'account_id' => self::getCOGSAccountId(),
            'entry_type' => 'debit',
            'amount' => $cogs,
            'description' => 'تكلفة البضاعة المباعة',
        ]);

        $entry->items()->create([
            'account_id' => self::getInventoryAccountId(),
            'entry_type' => 'credit',
            'amount' => $cogs,
            'description' => 'المخزون',
        ]);
    }

    $entry->post();
    return $entry;
}

public static function createPurchaseEntry($purchaseOrder)
{
    $entry = self::create([
        'entry_date' => $purchaseOrder->order_date,
        'reference_no' => 'JE-PURCHASE-' . $purchaseOrder->order_no,
        'description' => 'مشتريات - أمر شراء رقم: ' . $purchaseOrder->order_no,
        'entry_type' => 'auto',
        'reference_type' => 'purchase_order',
        'reference_id' => $purchaseOrder->id,
        'status' => 'draft',
        'created_by' => auth()->id(),
    ]);

    if ($purchaseOrder->payment_type == 'cash') {
        // نقدي: من ح/ المشتريات إلى ح/ النقدية
        $entry->items()->create([
            'account_id' => self::getPurchaseAccountId(),
            'entry_type' => 'debit',
            'amount' => $purchaseOrder->total_amount,
            'description' => 'مشتريات',
        ]);

        $entry->items()->create([
            'account_id' => self::getCashAccountId(),
            'entry_type' => 'credit',
            'amount' => $purchaseOrder->total_amount,
            'description' => 'نقدية',
        ]);
    } else {
        // آجل: من ح/ المشتريات إلى ح/ الموردين
        $entry->items()->create([
            'account_id' => self::getPurchaseAccountId(),
            'entry_type' => 'debit',
            'amount' => $purchaseOrder->total_amount,
            'description' => 'مشتريات',
        ]);

        $entry->items()->create([
            'account_id' => self::getSupplierAccountId($purchaseOrder->supplier_id),
            'entry_type' => 'credit',
            'amount' => $purchaseOrder->total_amount,
            'description' => 'موردين',
        ]);
    }

    // قيد المخزون
    $entry->items()->create([
        'account_id' => self::getInventoryAccountId(),
        'entry_type' => 'debit',
        'amount' => $purchaseOrder->total_amount,
        'description' => 'المخزون',
    ]);

    $entry->items()->create([
        'account_id' => self::getPurchaseAccountId(),
        'entry_type' => 'credit',
        'amount' => $purchaseOrder->total_amount,
        'description' => 'مشتريات',
    ]);

    $entry->post();
    return $entry;
}
```

### 4. تحديث نموذج JournalEntryItem ✅

#### التغييرات:
- **ملف**: `app/Models/JournalEntryItem.php`
- **إضافة**: حقول القيد المزدوج للـ fillable
- **إضافة**: حقول جديدة للربط والوصف

### 5. ربط القيد المزدوج بالمبيعات ✅

#### التغييرات:
- **ملف**: `app/Http/Controllers/SalesOrderController.php`
- **إضافة**: دالة `createJournalEntry()`
- **إضافة**: ربط تلقائي لإنشاء القيود المحاسبية عند تأكيد المبيعات

#### الكود المضاف:
```php
private function createJournalEntry($salesOrder)
{
    try {
        JournalEntry::createSalesEntry($salesOrder);
    } catch (\Exception $e) {
        \Log::error('خطأ في إنشاء القيد المحاسبي للمبيعات', [
            'error' => $e->getMessage(),
            'sales_order_id' => $salesOrder->id
        ]);
        throw new \Exception('فشل في إنشاء القيد المحاسبي: ' . $e->getMessage());
    }
}
```

### 6. ربط القيد المزدوج بالمشتريات ✅

#### التغييرات:
- **ملف**: `app/Http/Controllers/PurchaseOrderController.php`
- **إضافة**: دالة `createJournalEntry()`
- **إضافة**: ربط تلقائي لإنشاء القيود المحاسبية عند استلام المشتريات

#### الكود المضاف:
```php
private function createJournalEntry($purchaseOrder)
{
    try {
        JournalEntry::createPurchaseEntry($purchaseOrder);
    } catch (\Exception $e) {
        \Log::error('خطأ في إنشاء القيد المحاسبي للمشتريات', [
            'error' => $e->getMessage(),
            'purchase_order_id' => $purchaseOrder->id
        ]);
        throw new \Exception('فشل في إنشاء القيد المحاسبي: ' . $e->getMessage());
    }
}
```

### 7. إنشاء service للقيد المزدوج ✅

#### التغييرات:
- **ملف**: `app/Services/DoubleEntryService.php`
- **إضافة**: service شامل للقيد المزدوج
- **إضافة**: دوال إنشاء قيود مختلفة

#### الميزات:
- إنشاء قيود سندات القبض
- إنشاء قيود سندات الصرف
- إنشاء قيود مدفوعات العملاء
- إنشاء قيود مدفوعات الموردين
- إنشاء قيود تسوية المخزون
- حساب أرصدة الحسابات

#### الكود المضاف:
```php
class DoubleEntryService
{
    public static function createCashReceiptEntry($amount, $description, $referenceType = null, $referenceId = null)
    {
        return DB::transaction(function () use ($amount, $description, $referenceType, $referenceId) {
            $entry = JournalEntry::create([
                'entry_date' => now(),
                'reference_no' => 'JE-CASH-RCV-' . time(),
                'description' => $description,
                'entry_type' => 'auto',
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // من ح/ النقدية إلى ح/ الإيرادات
            $entry->items()->create([
                'account_id' => self::getCashAccountId(),
                'entry_type' => 'debit',
                'amount' => $amount,
                'description' => 'نقدية',
            ]);

            $entry->items()->create([
                'account_id' => self::getRevenueAccountId(),
                'entry_type' => 'credit',
                'amount' => $amount,
                'description' => 'إيرادات',
            ]);

            $entry->post();
            return $entry;
        });
    }

    public static function getAccountBalance($accountId, $asOfDate = null)
    {
        $query = JournalEntryItem::where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) {
                $q->where('is_posted', true);
            });

        if ($asOfDate) {
            $query->whereHas('journalEntry', function ($q) use ($asOfDate) {
                $q->where('entry_date', '<=', $asOfDate);
            });
        }

        $debits = $query->where('entry_type', 'debit')->sum('amount');
        $credits = $query->where('entry_type', 'credit')->sum('amount');

        return $debits - $credits;
    }
}
```

### 8. إنشاء controller لإدارة إغلاق الحسابات ✅

#### التغييرات:
- **ملف**: `app/Http/Controllers/AccountClosingController.php`
- **إضافة**: إدارة كاملة لإغلاق الحسابات
- **إضافة**: تقارير مالية

#### الميزات:
- إغلاق شهري للحسابات
- إغلاق سنوي للحسابات
- ميزان المراجعة
- قائمة الدخل
- الميزانية العمومية

#### الكود المضاف:
```php
class AccountClosingController extends Controller
{
    public function closeMonth(Request $request)
    {
        $validated = $request->validate([
            'closing_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $closingDate = $validated['closing_date'];
                
                // إغلاق حسابات الإيرادات
                $this->closeRevenueAccounts($closingDate, $validated['description'] ?? 'إغلاق شهري');
                
                // إغلاق حسابات المصروفات
                $this->closeExpenseAccounts($closingDate, $validated['description'] ?? 'إغلاق شهري');
                
                // إغلاق حساب الأرباح والخسائر
                $this->closeProfitLossAccount($closingDate, $validated['description'] ?? 'إغلاق شهري');
            });

            return redirect()->route('account_closing.index')
                ->with('success', 'تم إغلاق الحسابات بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في إغلاق الحسابات: ' . $e->getMessage());
        }
    }

    public function trialBalance(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now());
        
        $accounts = ChartOfAccount::with('type')->get();
        $trialBalance = [];

        foreach ($accounts as $account) {
            $balance = DoubleEntryService::getAccountBalance($account->id, $asOfDate);
            if ($balance != 0) {
                $trialBalance[] = [
                    'account' => $account,
                    'balance' => $balance,
                    'debit' => $balance > 0 ? $balance : 0,
                    'credit' => $balance < 0 ? abs($balance) : 0,
                ];
            }
        }

        $totalDebit = collect($trialBalance)->sum('debit');
        $totalCredit = collect($trialBalance)->sum('credit');

        return view('account_closing.trial_balance', compact('trialBalance', 'totalDebit', 'totalCredit', 'asOfDate'));
    }
}
```

## النتائج المتوقعة

### قبل الإصلاح:
- ❌ لا توجد قيود محاسبية تلقائية
- ❌ لا يوجد ربط بين المعاملات والحسابات
- ❌ لا يوجد نظام إغلاق للحسابات
- ❌ لا توجد تقارير مالية دقيقة
- ❌ لا يوجد حساب تكلفة البضاعة المباعة

### بعد الإصلاح:
- ✅ قيود محاسبية تلقائية للمبيعات والمشتريات
- ✅ ربط كامل بين المعاملات والحسابات
- ✅ نظام إغلاق شهري وسنوي للحسابات
- ✅ تقارير مالية دقيقة (ميزان المراجعة، قائمة الدخل، الميزانية العمومية)
- ✅ حساب تكلفة البضاعة المباعة تلقائياً
- ✅ نظام القيد المزدوج متكامل

## الاختبار المطلوب

### 1. اختبار القيود التلقائية للمبيعات:
1. إنشاء طلب بيع نقدي
2. تأكيد الطلب
3. التحقق من إنشاء القيد المحاسبي
4. التحقق من حساب تكلفة البضاعة المباعة

### 2. اختبار القيود التلقائية للمشتريات:
1. إنشاء أمر شراء آجل
2. استلام البضاعة
3. التحقق من إنشاء القيد المحاسبي
4. التحقق من قيد المخزون

### 3. اختبار إغلاق الحسابات:
1. إغلاق شهري للحسابات
2. التحقق من إغلاق الإيرادات والمصروفات
3. التحقق من حساب الأرباح والخسائر

### 4. اختبار التقارير المالية:
1. إنشاء ميزان المراجعة
2. إنشاء قائمة الدخل
3. إنشاء الميزانية العمومية
4. التحقق من دقة الأرقام

### 5. اختبار حساب الأرصدة:
1. حساب رصيد حساب النقدية
2. حساب رصيد حساب العملاء
3. حساب رصيد حساب الموردين
4. التحقق من تطابق الأرصدة

## الخطوات التالية

### المرحلة الرابعة: تحسين التقارير
1. تقارير الأرصدة المدينة والدائنة
2. تقارير الأعمار
3. تقارير الربحية
4. تقارير تحليلية

### المرحلة الخامسة: تحسينات إضافية
1. نظام الضرائب
2. نظام العملات المتعددة
3. نظام الفروع
4. نظام التدقيق والمراجعة

## ملاحظات مهمة

1. **النسخ الاحتياطي**: تم الاحتفاظ بنسخ احتياطية قبل التطبيق
2. **التسجيل**: جميع الأخطاء مسجلة في ملفات السجل
3. **الاختبار**: يجب اختبار جميع الوظائف قبل الانتقال للمرحلة التالية
4. **التوثيق**: جميع التغييرات موثقة في هذا الملف
5. **القيد المزدوج**: تم تطبيق نظام القيد المزدوج بالكامل
6. **إغلاق الحسابات**: تم إعداد نظام إغلاق شهري وسنوي
7. **التقارير المالية**: تم إعداد جميع التقارير المالية الأساسية 