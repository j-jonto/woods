<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'rate',
        'description',
        'is_active',
        'is_compound',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
        'is_compound' => 'boolean',
    ];

    /**
     * Get items that have this tax type.
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_taxes');
    }

    /**
     * Get sales taxes for this tax type.
     */
    public function salesTaxes()
    {
        return $this->hasMany(SalesTax::class);
    }

    /**
     * Get purchase taxes for this tax type.
     */
    public function purchaseTaxes()
    {
        return $this->hasMany(PurchaseTax::class);
    }

    /**
     * Calculate tax amount for a given amount.
     */
    public function calculateTax($amount)
    {
        return $amount * ($this->rate / 100);
    }

    /**
     * Scope a query to only include active tax types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted rate.
     */
    public function getFormattedRateAttribute()
    {
        return $this->rate . '%';
    }

    /**
     * Get display name with rate.
     */
    public function getDisplayNameAttribute()
    {
        return $this->name . ' (' . $this->formatted_rate . ')';
    }
} 