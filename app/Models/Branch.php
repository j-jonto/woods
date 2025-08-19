<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'manager_name',
        'is_active',
        'is_main_branch',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_main_branch' => 'boolean',
    ];

    /**
     * Get users assigned to this branch.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_users')
                    ->withPivot('is_manager', 'is_active')
                    ->withTimestamps();
    }

    /**
     * Get sales orders for this branch.
     */
    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    /**
     * Get purchase orders for this branch.
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get items for this branch.
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Get warehouses for this branch.
     */
    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    /**
     * Get customers for this branch.
     */
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get suppliers for this branch.
     */
    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    /**
     * Get treasury for this branch.
     */
    public function treasury()
    {
        return $this->hasOne(BranchTreasury::class);
    }

    /**
     * Get branch managers.
     */
    public function managers()
    {
        return $this->belongsToMany(User::class, 'branch_users')
                    ->wherePivot('is_manager', true)
                    ->wherePivot('is_active', true);
    }

    /**
     * Get active users for this branch.
     */
    public function activeUsers()
    {
        return $this->belongsToMany(User::class, 'branch_users')
                    ->wherePivot('is_active', true);
    }

    /**
     * Scope a query to only include active branches.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get main branch.
     */
    public static function getMainBranch()
    {
        return static::where('is_main_branch', true)->first();
    }

    /**
     * Check if this is the main branch.
     */
    public function isMainBranch()
    {
        return $this->is_main_branch;
    }

    /**
     * Get branch summary statistics.
     */
    public function getSummary()
    {
        return [
            'total_sales' => $this->salesOrders()->sum('total_amount'),
            'total_purchases' => $this->purchaseOrders()->sum('total_amount'),
            'total_customers' => $this->customers()->count(),
            'total_suppliers' => $this->suppliers()->count(),
            'total_items' => $this->items()->count(),
            'total_warehouses' => $this->warehouses()->count(),
            'total_users' => $this->activeUsers()->count(),
            'treasury_balance' => $this->treasury ? $this->treasury->current_balance : 0,
        ];
    }

    /**
     * Get monthly sales for this branch.
     */
    public function getMonthlySales($year = null, $month = null)
    {
        $query = $this->salesOrders();
        
        if ($year && $month) {
            $query->whereYear('order_date', $year)
                  ->whereMonth('order_date', $month);
        } elseif ($year) {
            $query->whereYear('order_date', $year);
        }
        
        return $query->sum('total_amount');
    }

    /**
     * Get monthly purchases for this branch.
     */
    public function getMonthlyPurchases($year = null, $month = null)
    {
        $query = $this->purchaseOrders();
        
        if ($year && $month) {
            $query->whereYear('order_date', $year)
                  ->whereMonth('order_date', $month);
        } elseif ($year) {
            $query->whereYear('order_date', $year);
        }
        
        return $query->sum('total_amount');
    }
} 