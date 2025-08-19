<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the audit logs for the user.
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get the journal entries created by the user.
     */
    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'created_by');
    }

    /**
     * Get the inventory transactions created by the user.
     */
    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'created_by');
    }

    /**
     * Get the production orders created by the user.
     */
    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class, 'created_by');
    }

    /**
     * Get the sales orders created by the user.
     */
    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class, 'created_by');
    }

    /**
     * Get the purchase orders created by the user.
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'created_by');
    }
} 