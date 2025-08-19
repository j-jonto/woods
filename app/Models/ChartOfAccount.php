<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'type',
        'parent_id',
        'level',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
    ];

    /**
     * Get the parent account.
     */
    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    /**
     * Get the child accounts.
     */
    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    /**
     * Get all child accounts recursively.
     */
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    /**
     * Get the journal entry items for this account.
     */
    public function journalEntryItems()
    {
        return $this->hasMany(JournalEntryItem::class, 'account_id');
    }

    /**
     * Get the total debit amount for a specific period.
     */
    public function getTotalDebit($startDate, $endDate)
    {
        return $this->journalEntryItems()
            ->whereHas('journalEntry', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('entry_date', [$startDate, $endDate])
                    ->where('status', 'posted');
            })
            ->sum('debit');
    }

    /**
     * Get the total credit amount for a specific period.
     */
    public function getTotalCredit($startDate, $endDate)
    {
        return $this->journalEntryItems()
            ->whereHas('journalEntry', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('entry_date', [$startDate, $endDate])
                    ->where('status', 'posted');
            })
            ->sum('credit');
    }

    /**
     * Get the balance for a specific period.
     */
    public function getBalance($startDate, $endDate)
    {
        $debit = $this->getTotalDebit($startDate, $endDate);
        $credit = $this->getTotalCredit($startDate, $endDate);

        if (in_array($this->type, ['asset', 'expense'])) {
            return $debit - $credit;
        }

        return $credit - $debit;
    }

    /**
     * Scope a query to only include active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include accounts of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include accounts at a specific level.
     */
    public function scopeAtLevel($query, $level)
    {
        return $query->where('level', $level);
    }
} 