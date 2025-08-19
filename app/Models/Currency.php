<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'symbol',
        'exchange_rate',
        'is_base_currency',
        'is_active',
        'decimal_places',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:6',
        'is_base_currency' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get sales orders in this currency.
     */
    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    /**
     * Get purchase orders in this currency.
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get items in this currency.
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Get treasury transactions in this currency.
     */
    public function treasuryTransactions()
    {
        return $this->hasMany(TreasuryTransaction::class);
    }

    /**
     * Get exchange rates where this currency is the source.
     */
    public function fromExchangeRates()
    {
        return $this->hasMany(ExchangeRate::class, 'from_currency_id');
    }

    /**
     * Get exchange rates where this currency is the target.
     */
    public function toExchangeRates()
    {
        return $this->hasMany(ExchangeRate::class, 'to_currency_id');
    }

    /**
     * Convert amount from this currency to base currency.
     */
    public function convertToBase($amount)
    {
        return $amount * $this->exchange_rate;
    }

    /**
     * Convert amount from base currency to this currency.
     */
    public function convertFromBase($amount)
    {
        return $amount / $this->exchange_rate;
    }

    /**
     * Get exchange rate to another currency.
     */
    public function getExchangeRateTo($targetCurrency)
    {
        if ($this->id === $targetCurrency->id) {
            return 1.0;
        }

        if ($this->is_base_currency) {
            return $targetCurrency->exchange_rate;
        }

        if ($targetCurrency->is_base_currency) {
            return 1 / $this->exchange_rate;
        }

        // For non-base currencies, calculate cross rate
        return $targetCurrency->exchange_rate / $this->exchange_rate;
    }

    /**
     * Convert amount to another currency.
     */
    public function convertTo($amount, $targetCurrency)
    {
        $rate = $this->getExchangeRateTo($targetCurrency);
        return $amount * $rate;
    }

    /**
     * Format amount with currency symbol.
     */
    public function formatAmount($amount)
    {
        return $this->symbol . ' ' . number_format($amount, $this->decimal_places);
    }

    /**
     * Scope a query to only include active currencies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get base currency.
     */
    public static function getBaseCurrency()
    {
        return static::where('is_base_currency', true)->first();
    }

    /**
     * Get default currency (base currency or first active currency).
     */
    public static function getDefaultCurrency()
    {
        $baseCurrency = static::getBaseCurrency();
        if ($baseCurrency) {
            return $baseCurrency;
        }

        return static::active()->first();
    }
} 