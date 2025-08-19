<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'expense_no', 'expense_type_id', 'amount', 'expense_date', 'description', 
        'reference_no', 'reference_type', 'reference_id', 'treasury_id', 'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($expense) {
            if (empty($expense->expense_no)) {
                $expense->expense_no = 'EXP-' . time() . '-' . rand(1000, 9999);
            }
        });
    }

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class, 'expense_type_id');
    }

    public function treasury()
    {
        return $this->belongsTo(Treasury::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

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

    // Scopes
    public function scopeByType($query, $typeId)
    {
        return $query->where('expense_type_id', $typeId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    public function scopeByTreasury($query, $treasuryId)
    {
        return $query->where('treasury_id', $treasuryId);
    }
} 