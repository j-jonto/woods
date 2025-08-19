<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Treasury extends Model
{
    protected $table = 'treasury';
    
    protected $fillable = [
        'name', 'opening_balance', 'current_balance', 'total_receipts', 
        'total_payments', 'description', 'is_active', 'currency_id',
        'base_opening_balance', 'base_current_balance'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'total_receipts' => 'decimal:2',
        'total_payments' => 'decimal:2',
        'base_opening_balance' => 'decimal:2',
        'base_current_balance' => 'decimal:2',
    ];

    /**
     * Get all transactions for this treasury
     */
    public function transactions()
    {
        return $this->hasMany(TreasuryTransaction::class);
    }

    /**
     * Get currency for this treasury
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Add receipt to treasury
     */
    public function addReceipt($amount, $description, $referenceType = null, $referenceId = null)
    {
        $this->current_balance += $amount;
        $this->total_receipts += $amount;
        $this->save();

        return $this->transactions()->create([
            'type' => 'receipt',
            'amount' => $amount,
            'balance_after' => $this->current_balance,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'transaction_date' => now()->toDateString(),
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Add payment from treasury
     */
    public function addPayment($amount, $description, $referenceType = null, $referenceId = null)
    {
        if ($this->current_balance < $amount) {
            throw new \Exception('رصيد الخزنة غير كافي');
        }

        $this->current_balance -= $amount;
        $this->total_payments += $amount;
        $this->save();

        return $this->transactions()->create([
            'type' => 'payment',
            'amount' => $amount,
            'balance_after' => $this->current_balance,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'transaction_date' => now()->toDateString(),
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Get treasury balance
     */
    public function getBalance()
    {
        return $this->current_balance;
    }

    /**
     * Get treasury summary
     */
    public function getSummary()
    {
        return [
            'opening_balance' => $this->opening_balance,
            'current_balance' => $this->current_balance,
            'total_receipts' => $this->total_receipts,
            'total_payments' => $this->total_payments,
            'net_change' => $this->total_receipts - $this->total_payments,
        ];
    }
}
