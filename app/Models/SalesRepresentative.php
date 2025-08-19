<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesRepresentative extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'email', 'address', 'commission_rate', 'is_active'
    ];

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'representative_id');
    }

    public function transactions()
    {
        return $this->hasMany(RepresentativeTransaction::class, 'representative_id');
    }

    public function getBalanceAttribute()
    {
        $goodsReceived = $this->transactions()->where('type', 'goods_received')->sum('amount');
        $payments = $this->transactions()->where('type', 'payment')->sum('amount');
        $commissions = $this->transactions()->where('type', 'commission')->sum('amount');
        
        return $goodsReceived - $payments + $commissions;
    }

    public function getTotalSalesAttribute()
    {
        return $this->salesOrders()->sum('total_amount');
    }

    public function getTotalCommissionAttribute()
    {
        return $this->salesOrders()->sum('total_amount') * ($this->commission_rate / 100);
    }
} 