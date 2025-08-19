<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreasuryTransaction extends Model
{
    protected $table = 'treasury_transactions';
    
    protected $fillable = [
        'treasury_id', 'type', 'amount', 'balance_after', 'reference_type', 
        'reference_id', 'description', 'transaction_date', 'created_by',
        'currency_id', 'exchange_rate', 'base_amount'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'date',
        'exchange_rate' => 'decimal:6',
        'base_amount' => 'decimal:2',
    ];

    /**
     * Get the treasury this transaction belongs to
     */
    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

    /**
     * Get the user who created this transaction
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get currency for this transaction
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the related model based on reference_type and reference_id
     */
    public function getRelatedModel()
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }

        $modelClass = 'App\\Models\\' . ucfirst($this->reference_type);
        
        if (class_exists($modelClass)) {
            return $modelClass::find($this->reference_id);
        }

        return null;
    }

    /**
     * Scope for receipts
     */
    public function scopeReceipts($query)
    {
        return $query->where('type', 'receipt');
    }

    /**
     * Scope for payments
     */
    public function scopePayments($query)
    {
        return $query->where('type', 'payment');
    }

    /**
     * Scope for date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }
}
