<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'barcode', 'name', 'type', 'category_id', 'unit_of_measure', 
        'standard_cost', 'selling_price', 'reorder_point', 'reorder_quantity', 'is_active',
        'currency_id', 'base_standard_cost', 'base_selling_price'
    ];

    protected $casts = [
        'standard_cost' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'reorder_point' => 'decimal:2',
        'reorder_quantity' => 'decimal:2',
        'is_active' => 'boolean',
        'base_standard_cost' => 'decimal:2',
        'base_selling_price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    // علاقات مع أوامر البيع
    public function salesOrderItems()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function salesOrders()
    {
        return $this->belongsToMany(SalesOrder::class, 'sales_order_items', 'item_id', 'sales_order_id');
    }

    // علاقات مع أوامر الشراء
    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function purchaseOrders()
    {
        return $this->belongsToMany(PurchaseOrder::class, 'purchase_order_items', 'item_id', 'purchase_order_id');
    }

    // علاقات مع فواتير الشراء
    public function purchaseInvoiceItems()
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function purchaseInvoices()
    {
        return $this->belongsToMany(PurchaseInvoice::class, 'purchase_invoice_items', 'item_id', 'purchase_invoice_id');
    }

    // علاقات مع أوامر الإنتاج
    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class);
    }

    public function productionMaterials()
    {
        return $this->hasMany(ProductionMaterial::class, 'material_id');
    }

    // علاقات مع قوائم المواد
    public function billOfMaterialsAsFinished()
    {
        return $this->hasMany(BillOfMaterial::class, 'finished_good_id');
    }

    public function billOfMaterialsAsRaw()
    {
        return $this->hasMany(BillOfMaterial::class, 'raw_material_id');
    }

    // Accessors
    public function getAvailableStockAttribute()
    {
        return $this->inventoryTransactions()
            ->selectRaw('SUM(CASE WHEN type IN ("receipt", "transfer") THEN quantity ELSE -quantity END) as stock')
            ->value('stock') ?? 0;
    }

    public function getCurrentStockAttribute()
    {
        return $this->available_stock;
    }

    public function getCurrentStock()
    {
        return $this->available_stock;
    }

    public function getAvailableStockByWarehouseAttribute()
    {
        return $this->inventoryTransactions()
            ->selectRaw('warehouse_id, SUM(CASE WHEN type IN ("receipt", "transfer") THEN quantity ELSE -quantity END) as stock')
            ->groupBy('warehouse_id')
            ->pluck('stock', 'warehouse_id')
            ->toArray();
    }

    public function getTotalReceiptsAttribute()
    {
        return $this->inventoryTransactions()
            ->whereIn('type', ['receipt', 'transfer'])
            ->sum('quantity');
    }

    public function getTotalIssuesAttribute()
    {
        return $this->inventoryTransactions()
            ->whereIn('type', ['issue', 'sale'])
            ->sum('quantity');
    }

    // دوال حساب الأرباح
    public function getTotalCostAttribute()
    {
        return $this->total_receipts * $this->standard_cost;
    }

    public function getTotalRevenueAttribute()
    {
        return $this->total_issues * $this->selling_price;
    }

    public function getGrossProfitAttribute()
    {
        return $this->total_revenue - $this->total_cost;
    }

    public function getProfitMarginAttribute()
    {
        if ($this->total_revenue > 0) {
            return ($this->gross_profit / $this->total_revenue) * 100;
        }
        return 0;
    }

    public function getProfitPerUnitAttribute()
    {
        return $this->selling_price - $this->standard_cost;
    }

    public function getIsLowStockAttribute()
    {
        return $this->available_stock <= $this->reorder_point;
    }

    public function getIsOutOfStockAttribute()
    {
        return $this->available_stock <= 0;
    }

    public function getStockValueAttribute()
    {
        return $this->available_stock * $this->standard_cost;
    }

    public function getStockValueByCurrencyAttribute()
    {
        if ($this->currency) {
            return $this->available_stock * $this->standard_cost * $this->currency->exchange_rate;
        }
        return $this->stock_value;
    }

    public function getLastTransactionDateAttribute()
    {
        return $this->inventoryTransactions()
            ->latest()
            ->value('transaction_date');
    }

    public function getAverageCostAttribute()
    {
        $totalQuantity = $this->inventoryTransactions()
            ->whereIn('type', ['receipt', 'transfer'])
            ->sum('quantity');
        
        if ($totalQuantity > 0) {
            $totalCost = $this->inventoryTransactions()
                ->whereIn('type', ['receipt', 'transfer'])
                ->sum(\DB::raw('quantity * unit_cost'));
            
            return $totalCost / $totalQuantity;
        }
        
        return $this->standard_cost;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('(SELECT SUM(CASE WHEN type IN ("receipt", "transfer") THEN quantity ELSE -quantity END) FROM inventory_transactions WHERE inventory_transactions.item_id = items.id) <= reorder_point');
    }

    public function scopeOutOfStock($query)
    {
        return $query->whereRaw('(SELECT SUM(CASE WHEN type IN ("receipt", "transfer") THEN quantity ELSE -quantity END) FROM inventory_transactions WHERE inventory_transactions.item_id = items.id) <= 0');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->whereHas('inventoryTransactions', function ($q) use ($warehouseId) {
            $q->where('warehouse_id', $warehouseId);
        });
    }

    public function scopeExpensive($query, $minPrice = 1000)
    {
        return $query->where('standard_cost', '>=', $minPrice);
    }

    public function scopeCheap($query, $maxPrice = 100)
    {
        return $query->where('standard_cost', '<=', $maxPrice);
    }

    public function scopeHighProfit($query, $minProfit = 50)
    {
        return $query->whereRaw('(selling_price - standard_cost) >= ?', [$minProfit]);
    }

    // Methods
    public function updateStock($quantity, $type, $warehouseId = null, $referenceType = null, $referenceId = null)
    {
        return $this->inventoryTransactions()->create([
            'type' => $type,
            'quantity' => $quantity,
            'warehouse_id' => $warehouseId,
            'unit_cost' => $this->standard_cost,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'reference_no' => 'INV-' . time() . '-' . rand(1000, 9999),
            'transaction_date' => now(),
            'created_by' => auth()->id(),
        ]);
    }

    public function addStock($quantity, $warehouseId = null, $referenceType = null, $referenceId = null)
    {
        return $this->updateStock($quantity, 'receipt', $warehouseId, $referenceType, $referenceId);
    }

    public function removeStock($quantity, $warehouseId = null, $referenceType = null, $referenceId = null)
    {
        return $this->updateStock($quantity, 'issue', $warehouseId, $referenceType, $referenceId);
    }

    public function transferStock($quantity, $fromWarehouseId, $toWarehouseId, $referenceType = null, $referenceId = null)
    {
        // Remove from source warehouse
        $this->updateStock($quantity, 'transfer_out', $fromWarehouseId, $referenceType, $referenceId);
        
        // Add to destination warehouse
        return $this->updateStock($quantity, 'transfer_in', $toWarehouseId, $referenceType, $referenceId);
    }

    public function getStockByWarehouse($warehouseId)
    {
        return $this->inventoryTransactions()
            ->where('warehouse_id', $warehouseId)
            ->selectRaw('SUM(CASE WHEN type IN ("receipt", "transfer_in") THEN quantity ELSE -quantity END) as stock')
            ->value('stock') ?? 0;
    }

    public function getStockMovement($startDate = null, $endDate = null)
    {
        $query = $this->inventoryTransactions();
        
        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }
        
        return $query->orderBy('transaction_date')->get();
    }

    public function getStockHistory($days = 30)
    {
        $startDate = now()->subDays($days);
        
        return $this->inventoryTransactions()
            ->where('transaction_date', '>=', $startDate)
            ->orderBy('transaction_date')
            ->get();
    }

    /**
     * التحقق من إمكانية حذف المنتج
     */
    public function canBeDeleted()
    {
        // التحقق من وجود معاملات مخزون
        if ($this->inventoryTransactions()->count() > 0) {
            return false;
        }

        // التحقق من وجود في طلبات البيع
        if ($this->salesOrderItems()->count() > 0) {
            return false;
        }

        // التحقق من وجود في طلبات الشراء
        if ($this->purchaseOrderItems()->count() > 0) {
            return false;
        }

        // التحقق من وجود في فواتير الشراء
        if ($this->purchaseInvoiceItems()->count() > 0) {
            return false;
        }

        // التحقق من وجود في أوامر الإنتاج
        if ($this->productionOrders()->count() > 0) {
            return false;
        }

        // التحقق من وجود في قوائم المواد
        if ($this->billOfMaterialsAsFinished()->count() > 0 || $this->billOfMaterialsAsRaw()->count() > 0) {
            return false;
        }

        return true;
    }

    /**
     * الحصول على رسالة توضح سبب عدم إمكانية الحذف
     */
    public function getDeletionErrorMessage()
    {
        $errors = [];

        if ($this->inventoryTransactions()->count() > 0) {
            $errors[] = 'يوجد معاملات مخزون مرتبطة بهذا المنتج';
        }

        if ($this->salesOrderItems()->count() > 0) {
            $errors[] = 'يوجد طلبات بيع تحتوي على هذا المنتج';
        }

        if ($this->purchaseOrderItems()->count() > 0) {
            $errors[] = 'يوجد طلبات شراء تحتوي على هذا المنتج';
        }

        if ($this->purchaseInvoiceItems()->count() > 0) {
            $errors[] = 'يوجد فواتير شراء تحتوي على هذا المنتج';
        }

        if ($this->productionOrders()->count() > 0) {
            $errors[] = 'يوجد أوامر إنتاج مرتبطة بهذا المنتج';
        }

        if ($this->billOfMaterialsAsFinished()->count() > 0) {
            $errors[] = 'يوجد قوائم مواد تستخدم هذا المنتج كمنتج نهائي';
        }

        if ($this->billOfMaterialsAsRaw()->count() > 0) {
            $errors[] = 'يوجد قوائم مواد تستخدم هذا المنتج كمادة خام';
        }

        return implode(', ', $errors);
    }

    /**
     * حذف تدريجي للمنتج مع التحقق من العلاقات
     */
    public function safeDelete()
    {
        if (!$this->canBeDeleted()) {
            throw new \Exception('لا يمكن حذف هذا المنتج: ' . $this->getDeletionErrorMessage());
        }

        return $this->delete();
    }
} 