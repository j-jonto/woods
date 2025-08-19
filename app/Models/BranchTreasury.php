<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchTreasury extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'opening_balance',
        'current_balance',
        'currency_id',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    /**
     * Get the branch this treasury belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the currency for this treasury.
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Add receipt to treasury.
     */
    public function addReceipt($amount, $description = null, $referenceType = null, $referenceId = null)
    {
        $this->current_balance += $amount;
        $this->save();

        // Log the transaction
        \Log::info('Branch treasury receipt added', [
            'branch_id' => $this->branch_id,
            'amount' => $amount,
            'description' => $description,
            'new_balance' => $this->current_balance,
        ]);

        return $this;
    }

    /**
     * Add payment from treasury.
     */
    public function addPayment($amount, $description = null, $referenceType = null, $referenceId = null)
    {
        if ($this->current_balance < $amount) {
            throw new \Exception('Insufficient balance in branch treasury');
        }

        $this->current_balance -= $amount;
        $this->save();

        // Log the transaction
        \Log::info('Branch treasury payment made', [
            'branch_id' => $this->branch_id,
            'amount' => $amount,
            'description' => $description,
            'new_balance' => $this->current_balance,
        ]);

        return $this;
    }

    /**
     * Get balance in specified currency.
     */
    public function getBalanceInCurrency($currencyId)
    {
        if (!$this->currency_id || $this->currency_id == $currencyId) {
            return $this->current_balance;
        }

        $fromCurrency = Currency::find($this->currency_id);
        $toCurrency = Currency::find($currencyId);

        if (!$fromCurrency || !$toCurrency) {
            return $this->current_balance;
        }

        return $fromCurrency->convertTo($this->current_balance, $toCurrency);
    }

    /**
     * Get formatted balance.
     */
    public function getFormattedBalanceAttribute()
    {
        if ($this->currency) {
            return $this->currency->formatAmount($this->current_balance);
        }
        
        return number_format($this->current_balance, 2);
    }

    /**
     * Check if treasury has sufficient balance.
     */
    public function hasSufficientBalance($amount)
    {
        return $this->current_balance >= $amount;
    }

    /**
     * Get available balance (current balance minus any pending transactions).
     */
    public function getAvailableBalance()
    {
        // For now, return current balance. In a real system, you might subtract pending transactions
        return $this->current_balance;
    }
} 