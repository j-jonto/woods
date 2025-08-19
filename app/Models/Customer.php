<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'contact_person', 'email', 'phone', 'address', 
        'credit_limit', 'opening_balance', 'current_balance', 'total_receivables',
        'total_payments', 'last_transaction_date', 'is_active'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'total_receivables' => 'decimal:2',
        'total_payments' => 'decimal:2',
        'last_transaction_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Accessors
    public function getTotalSalesAttribute()
    {
        return $this->salesOrders()->sum('total_amount');
    }

    public function getTotalPaymentsAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getBalanceAttribute()
    {
        return $this->current_balance;
    }

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
            'type' => 'receipt',
            'reference' => $referenceType,
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
            'type' => 'disbursement',
            'reference' => $referenceType,
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

    public function getIsOverLimitAttribute()
    {
        return $this->balance > $this->credit_limit;
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

    public function scopeOverLimit($query)
    {
        return $query->whereRaw('(SELECT COALESCE(SUM(total_amount), 0) FROM sales_orders WHERE sales_orders.customer_id = customers.id) - (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.customer_id = customers.id) > credit_limit');
    }

    public function scopeByBalance($query, $minBalance = null, $maxBalance = null)
    {
        if ($minBalance !== null) {
            $query->whereRaw('(SELECT COALESCE(SUM(total_amount), 0) FROM sales_orders WHERE sales_orders.customer_id = customers.id) - (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.customer_id = customers.id) >= ?', [$minBalance]);
        }
        if ($maxBalance !== null) {
            $query->whereRaw('(SELECT COALESCE(SUM(total_amount), 0) FROM sales_orders WHERE sales_orders.customer_id = customers.id) - (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.customer_id = customers.id) <= ?', [$maxBalance]);
        }
        return $query;
    }
} 