<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Supplier;

class JournalEntry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'entry_date',
        'reference_no',
        'description',
        'entry_type',
        'reference_type',
        'reference_id',
        'is_posted',
        'posted_at',
        'posted_by',
        'status',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'entry_date' => 'date',
        'is_posted' => 'boolean',
        'posted_at' => 'datetime',
    ];

    /**
     * Get the user who created the journal entry.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the journal entry items.
     */
    public function items()
    {
        return $this->hasMany(JournalEntryItem::class);
    }

    /**
     * Get the total debit amount.
     */
    public function getTotalDebit()
    {
        return $this->items()->sum('debit');
    }

    /**
     * Get the total credit amount.
     */
    public function getTotalCredit()
    {
        return $this->items()->sum('credit');
    }

    /**
     * Check if the journal entry is balanced.
     */
    public function isBalanced()
    {
        return $this->getTotalDebit() == $this->getTotalCredit();
    }

    /**
     * Post the journal entry.
     */
    public function post()
    {
        if (!$this->isBalanced()) {
            throw new \Exception('Journal entry is not balanced.');
        }

        $this->update([
            'status' => 'posted',
        ]);
    }

    /**
     * Create automatic journal entry for sales.
     */
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
            // نقدي: من ح/ النقدية (مدين) إلى ح/ المبيعات (دائن)
            $entry->items()->create([
                'account_id' => self::getCashAccountId(),
                'debit' => $salesOrder->total_amount,
                'credit' => 0,
                'description' => 'نقدية',
            ]);

            $entry->items()->create([
                'account_id' => self::getSalesAccountId(),
                'debit' => 0,
                'credit' => $salesOrder->total_amount,
                'description' => 'مبيعات',
            ]);
        } else {
            // آجل: من ح/ العملاء (مدين) إلى ح/ المبيعات (دائن)
            $entry->items()->create([
                'account_id' => self::getCustomerAccountId($salesOrder->customer_id),
                'debit' => $salesOrder->total_amount,
                'credit' => 0,
                'description' => 'عملاء',
            ]);

            $entry->items()->create([
                'account_id' => self::getSalesAccountId(),
                'debit' => 0,
                'credit' => $salesOrder->total_amount,
                'description' => 'مبيعات',
            ]);
        }

        // قيد تكلفة البضاعة المباعة
        if ($cogs > 0) {
            $entry->items()->create([
                'account_id' => self::getCOGSAccountId(),
                'debit' => $cogs,
                'credit' => 0,
                'description' => 'تكلفة البضاعة المباعة',
            ]);

            $entry->items()->create([
                'account_id' => self::getInventoryFinishedGoodsAccountId(),
                'debit' => 0,
                'credit' => $cogs,
                'description' => 'المخزون',
            ]);
        }

        $entry->post();
        return $entry;
    }

    /**
     * Create automatic journal entry for purchases.
     */
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
            // نقدي: من ح/ المخزون (مدين) إلى ح/ النقدية (دائن)
            $entry->items()->create([
                'account_id' => self::getInventoryRawMaterialsAccountId(),
                'debit' => $purchaseOrder->total_amount,
                'credit' => 0,
                'description' => 'مخزون',
            ]);

            $entry->items()->create([
                'account_id' => self::getCashAccountId(),
                'debit' => 0,
                'credit' => $purchaseOrder->total_amount,
                'description' => 'نقدية',
            ]);
        } else {
            // آجل: من ح/ المخزون (مدين) إلى ح/ الموردين (دائن)
            $entry->items()->create([
                'account_id' => self::getInventoryRawMaterialsAccountId(),
                'debit' => $purchaseOrder->total_amount,
                'credit' => 0,
                'description' => 'مخزون',
            ]);

            $entry->items()->create([
                'account_id' => self::getSupplierAccountId($purchaseOrder->supplier_id),
                'debit' => 0,
                'credit' => $purchaseOrder->total_amount,
                'description' => 'موردين',
            ]);
        }

        $entry->post();
        return $entry;
    }

    /**
     * Helper methods to get account IDs.
     */
    private static function getCashAccountId()
    {
        $acc = ChartOfAccount::where('code', '10100')->first();
        if (!$acc) {
            throw new \Exception('الحساب 10100 (النقدية) غير موجود. يرجى تهيئة دليل الحسابات.');
        }
        return $acc->id;
    }

    private static function getSalesAccountId()
    {
        $acc = ChartOfAccount::where('code', '40100')->first();
        if (!$acc) {
            throw new \Exception('الحساب 40100 (المبيعات) غير موجود. يرجى تهيئة دليل الحسابات.');
        }
        return $acc->id;
    }

    private static function getPurchaseAccountId()
    {
        $acc = ChartOfAccount::where('code', '51000')->first();
        if (!$acc) {
            throw new \Exception('الحساب 51000 (المشتريات) غير موجود. يرجى تهيئة دليل الحسابات.');
        }
        return $acc->id;
    }

    private static function getInventoryAccountId()
    {
        $acc = ChartOfAccount::where('code', '10300')->first();
        if (!$acc) {
            throw new \Exception('الحساب 10300 (مخزون مواد) غير موجود. يرجى تهيئة دليل الحسابات.');
        }
        return $acc->id;
    }

    private static function getInventoryRawMaterialsAccountId()
    {
        return ChartOfAccount::where('code', '10300')->first()->id ?? self::getInventoryAccountId();
    }

    private static function getInventoryFinishedGoodsAccountId()
    {
        return ChartOfAccount::where('code', '10500')->first()->id ?? self::getInventoryAccountId();
    }

    private static function getCOGSAccountId()
    {
        $acc = ChartOfAccount::where('code', '50100')->first();
        if (!$acc) {
            throw new \Exception('الحساب 50100 (تكلفة البضاعة المباعة) غير موجود. يرجى تهيئة دليل الحسابات.');
        }
        return $acc->id;
    }

    private static function getCustomerAccountId($customerId)
    {
        $customer = Customer::find($customerId);
        $acc = ChartOfAccount::where('code', '10200')->first();
        if (!$acc) {
            throw new \Exception('الحساب 10200 (العملاء) غير موجود. يرجى تهيئة دليل الحسابات.');
        }
        return $acc->id;
    }

    private static function getSupplierAccountId($supplierId)
    {
        $supplier = Supplier::find($supplierId);
        $acc = ChartOfAccount::where('code', '20100')->first();
        if (!$acc) {
            throw new \Exception('الحساب 20100 (الموردون) غير موجود. يرجى تهيئة دليل الحسابات.');
        }
        return $acc->id;
    }

    /**
     * Cancel the journal entry.
     */
    public function cancel()
    {
        if ($this->status === 'posted') {
            throw new \Exception('Cannot cancel a posted journal entry.');
        }

        $this->status = 'cancelled';
        $this->save();
    }

    /**
     * Scope a query to only include posted entries.
     */
    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    /**
     * Scope a query to only include draft entries.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include cancelled entries.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to only include entries within a date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }
} 