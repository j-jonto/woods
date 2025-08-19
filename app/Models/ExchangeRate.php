<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_currency_id',
        'to_currency_id',
        'rate',
        'rate_date',
        'source',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'rate_date' => 'date',
    ];

    /**
     * Get the source currency.
     */
    public function fromCurrency()
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    /**
     * Get the target currency.
     */
    public function toCurrency()
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }

    /**
     * Get the inverse rate (1/rate).
     */
    public function getInverseRateAttribute()
    {
        return $this->rate > 0 ? 1 / $this->rate : 0;
    }

    /**
     * Scope a query to only include rates for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('rate_date', $date);
    }

    /**
     * Scope a query to only include rates between two currencies.
     */
    public function scopeBetweenCurrencies($query, $fromCurrencyId, $toCurrencyId)
    {
        return $query->where('from_currency_id', $fromCurrencyId)
                    ->where('to_currency_id', $toCurrencyId);
    }

    /**
     * Get the latest exchange rate between two currencies.
     */
    public static function getLatestRate($fromCurrencyId, $toCurrencyId, $date = null)
    {
        $query = static::betweenCurrencies($fromCurrencyId, $toCurrencyId);
        
        if ($date) {
            $query->forDate($date);
        }
        
        return $query->orderBy('rate_date', 'desc')->first();
    }

    /**
     * Get exchange rate for a specific date, or latest if not found.
     */
    public static function getRateForDate($fromCurrencyId, $toCurrencyId, $date)
    {
        $rate = static::getLatestRate($fromCurrencyId, $toCurrencyId, $date);
        
        if (!$rate) {
            // If no rate for specific date, get latest rate
            $rate = static::getLatestRate($fromCurrencyId, $toCurrencyId);
        }
        
        return $rate;
    }
} 