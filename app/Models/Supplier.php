<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'contact_person', 'email', 'phone', 'address',
        'opening_balance', 'current_balance', 'total_payables', 'total_payments',
        'last_transaction_date', 'is_active'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'total_payables' => 'decimal:2',
        'total_payments' => 'decimal:2',
        'last_transaction_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function purchaseInvoices()
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Accessors
    public function getTotalPurchasesAttribute()
    {
        return $this->purchaseOrders()->sum('total_amount');
    }

    public function getTotalPaymentsAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getBalanceAttribute()
    {
        return $this->current_balance;
    }

    public function addPayable($amount, $description = null, $referenceType = null, $referenceId = null)
    {
        $this->increment('current_balance', $amount);
        $this->increment('total_payables', $amount);
        $this->update(['last_transaction_date' => now()]);

        // تسجيل المعاملة في جدول مدفوعات الموردين
        SupplierPayment::create([
            'payment_no' => 'SP-' . date('Ymd') . '-' . time(),
            'supplier_id' => $this->id,
            'amount' => $amount,
            'payment_date' => now(),
            'payment_method' => 'credit',
            'notes' => $description ?? 'مشتريات آجلة',
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
            'payment_no' => 'SP-' . date('Ymd') . '-' . time(),
            'supplier_id' => $this->id,
            'amount' => $amount,
            'payment_date' => now(),
            'payment_method' => 'cash',
            'notes' => $description ?? 'دفع للمورد',
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

    public function getIsActiveAttribute()
    {
        return $this->attributes['is_active'] ?? true;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByBalance($query, $minBalance = null, $maxBalance = null)
    {
        if ($minBalance !== null) {
            $query->whereRaw('(SELECT COALESCE(SUM(total_amount), 0) FROM purchase_orders WHERE purchase_orders.supplier_id = suppliers.id) - (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.supplier_id = suppliers.id) >= ?', [$minBalance]);
        }
        if ($maxBalance !== null) {
            $query->whereRaw('(SELECT COALESCE(SUM(total_amount), 0) FROM purchase_orders WHERE purchase_orders.supplier_id = suppliers.id) - (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.supplier_id = suppliers.id) <= ?', [$maxBalance]);
        }
        return $query;
    }
} 