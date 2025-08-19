<?php

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Treasury;
use Illuminate\Support\Facades\DB;

class DoubleEntryService
{
    /**
     * Create journal entry for cash receipt.
     */
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

            // من ح/ النقدية (مدين) إلى ح/ الإيرادات (دائن)
            $entry->items()->create([
                'account_id' => self::getCashAccountId(),
                'debit' => $amount,
                'credit' => 0,
                'description' => 'نقدية',
            ]);

            $entry->items()->create([
                'account_id' => self::getRevenueAccountId(),
                'debit' => 0,
                'credit' => $amount,
                'description' => 'إيرادات',
            ]);

            $entry->post();
            return $entry;
        });
    }

    /**
     * Create journal entry for cash payment.
     */
    public static function createCashPaymentEntry($amount, $description, $referenceType = null, $referenceId = null)
    {
        return DB::transaction(function () use ($amount, $description, $referenceType, $referenceId) {
            $entry = JournalEntry::create([
                'entry_date' => now(),
                'reference_no' => 'JE-CASH-PAY-' . time(),
                'description' => $description,
                'entry_type' => 'auto',
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // من ح/ المصروفات (مدين) إلى ح/ النقدية (دائن)
            $entry->items()->create([
                'account_id' => self::getExpenseAccountId(),
                'debit' => $amount,
                'credit' => 0,
                'description' => 'مصروفات',
            ]);

            $entry->items()->create([
                'account_id' => self::getCashAccountId(),
                'debit' => 0,
                'credit' => $amount,
                'description' => 'نقدية',
            ]);

            $entry->post();
            return $entry;
        });
    }

    /**
     * Create journal entry for customer payment.
     */
    public static function createCustomerPaymentEntry($customer, $amount, $description, $referenceType = null, $referenceId = null)
    {
        return DB::transaction(function () use ($customer, $amount, $description, $referenceType, $referenceId) {
            $entry = JournalEntry::create([
                'entry_date' => now(),
                'reference_no' => 'JE-CUST-PAY-' . time(),
                'description' => $description,
                'entry_type' => 'auto',
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // من ح/ النقدية (مدين) إلى ح/ العملاء (دائن)
            $entry->items()->create([
                'account_id' => self::getCashAccountId(),
                'debit' => $amount,
                'credit' => 0,
                'description' => 'نقدية',
            ]);

            $entry->items()->create([
                'account_id' => self::getCustomerAccountId($customer->id),
                'debit' => 0,
                'credit' => $amount,
                'description' => 'عملاء',
            ]);

            $entry->post();
            return $entry;
        });
    }

    /**
     * Create journal entry for supplier payment.
     */
    public static function createSupplierPaymentEntry($supplier, $amount, $description, $referenceType = null, $referenceId = null)
    {
        return DB::transaction(function () use ($supplier, $amount, $description, $referenceType, $referenceId) {
            $entry = JournalEntry::create([
                'entry_date' => now(),
                'reference_no' => 'JE-SUP-PAY-' . time(),
                'description' => $description,
                'entry_type' => 'auto',
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // من ح/ الموردين (مدين) إلى ح/ النقدية (دائن)
            $entry->items()->create([
                'account_id' => self::getSupplierAccountId($supplier->id),
                'debit' => $amount,
                'credit' => 0,
                'description' => 'موردين',
            ]);

            $entry->items()->create([
                'account_id' => self::getCashAccountId(),
                'debit' => 0,
                'credit' => $amount,
                'description' => 'نقدية',
            ]);

            $entry->post();
            return $entry;
        });
    }

    /**
     * Create journal entry for inventory adjustment.
     */
    public static function createInventoryAdjustmentEntry($item, $quantity, $unitCost, $adjustmentType, $description)
    {
        return DB::transaction(function () use ($item, $quantity, $unitCost, $adjustmentType, $description) {
            $amount = $quantity * $unitCost;
            
            $entry = JournalEntry::create([
                'entry_date' => now(),
                'reference_no' => 'JE-INV-ADJ-' . time(),
                'description' => $description,
                'entry_type' => 'adjustment',
                'reference_type' => 'inventory_adjustment',
                'reference_id' => $item->id,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            if ($adjustmentType == 'increase') {
                // زيادة المخزون: من ح/ المخزون (مدين) إلى ح/ فروق/تكلفة المخزون (دائن)
                $entry->items()->create([
                    'account_id' => self::getInventoryAccountId(),
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => 'مخزون',
                ]);

                $entry->items()->create([
                    'account_id' => self::getInventoryCostAccountId(),
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => 'تكلفة المخزون',
                ]);
            } else {
                // نقصان المخزون: من ح/ تكلفة المخزون (مدين) إلى ح/ المخزون (دائن)
                $entry->items()->create([
                    'account_id' => self::getInventoryCostAccountId(),
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => 'تكلفة المخزون',
                ]);

                $entry->items()->create([
                    'account_id' => self::getInventoryAccountId(),
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => 'مخزون',
                ]);
            }

            $entry->post();
            return $entry;
        });
    }

    /**
     * Get account balance.
     */
    public static function getAccountBalance($accountId, $asOfDate = null)
    {
        $query = JournalEntryItem::where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) {
                $q->where('status', 'posted');
            });

        if ($asOfDate) {
            $query->whereHas('journalEntry', function ($q) use ($asOfDate) {
                $q->where('entry_date', '<=', $asOfDate);
            });
        }

        $debits = (clone $query)->sum('debit');
        $credits = (clone $query)->sum('credit');

        $account = ChartOfAccount::find($accountId);
        if (!$account) {
            return $debits - $credits;
        }

        // إرجاع الرصيد وفق طبيعة الحساب
        if (in_array($account->type, ['asset', 'expense'])) {
            return $debits - $credits;
        }
        return $credits - $debits;
    }

    /**
     * Helper methods to get account IDs.
     */
    private static function getCashAccountId()
    {
        $acc = ChartOfAccount::where('code', '10100')->first();
        if (!$acc) { throw new \Exception('الحساب 10100 (النقدية) غير موجود.'); }
        return $acc->id; // النقدية
    }

    private static function getRevenueAccountId()
    {
        $acc = ChartOfAccount::where('code', '40100')->first();
        if (!$acc) { throw new \Exception('الحساب 40100 (الإيرادات) غير موجود.'); }
        return $acc->id; // إيرادات المبيعات
    }

    private static function getExpenseAccountId()
    {
        $acc = ChartOfAccount::where('code', '50100')->first();
        if (!$acc) { throw new \Exception('الحساب 50100 (التكلفة/المصروفات) غير موجود.'); }
        return $acc->id; // تكلفة/مصروفات
    }

    private static function getInventoryAccountId()
    {
        $acc = ChartOfAccount::where('code', '10300')->first();
        if (!$acc) { throw new \Exception('الحساب 10300 (المخزون) غير موجود.'); }
        return $acc->id; // مخزون
    }

    private static function getInventoryCostAccountId()
    {
        $acc = ChartOfAccount::where('code', '50100')->first();
        if (!$acc) { throw new \Exception('الحساب 50100 (تكلفة البضاعة المباعة) غير موجود.'); }
        return $acc->id; // تكلفة البضاعة المباعة
    }

    private static function getCustomerAccountId($customerId)
    {
        $acc = ChartOfAccount::where('code', '10200')->first();
        if (!$acc) { throw new \Exception('الحساب 10200 (الذمم المدينة) غير موجود.'); }
        return $acc->id; // الذمم المدينة
    }

    private static function getSupplierAccountId($supplierId)
    {
        $acc = ChartOfAccount::where('code', '20100')->first();
        if (!$acc) { throw new \Exception('الحساب 20100 (الذمم الدائنة) غير موجود.'); }
        return $acc->id; // الذمم الدائنة
    }
} 