<?php

namespace App\Http\Controllers;

use App\Models\ProductionOrder;
use App\Models\BillOfMaterial;
use App\Models\Item;
use App\Models\WorkCenter;
use App\Models\ProductionMaterial;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\JournalEntry;

class ProductionOrderController extends Controller
{
    private static function getInventoryFinishedGoodsAccountId()
    {
        $acc = \App\Models\ChartOfAccount::where('code', '10500')->first();
        if (!$acc) {
            throw new \Exception('الحساب 10500 (مخزون تام) غير موجود. يرجى تهيئة دليل الحسابات.');
        }
        return $acc->id;
    }

    private static function getInventoryAccountId()
    {
        $acc = \App\Models\ChartOfAccount::where('code', '10300')->first();
        if (!$acc) {
            throw new \Exception('الحساب 10300 (مخزون مواد خام) غير موجود. يرجى تهيئة دليل الحسابات.');
        }
        return $acc->id;
    }
    public function index()
    {
        $productionOrders = ProductionOrder::with(['item', 'billOfMaterial', 'workCenter', 'creator'])
            ->orderByDesc('order_date')
            ->paginate(20);
        return view('production_orders.index', compact('productionOrders'));
    }

    public function create()
    {
        $boms = BillOfMaterial::with(['finishedGood', 'rawMaterial'])->get();
        $workCenters = WorkCenter::all();
        $finishedGoods = Item::where('type', 'finished_goods')->get();
        return view('production_orders.create', compact('boms', 'workCenters', 'finishedGoods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_no' => 'required|unique:production_orders',
            'item_id' => 'required|exists:items,id',
            'bill_of_material_id' => 'nullable|exists:bill_of_materials,id',
            'work_center_id' => 'nullable|exists:work_centers,id',
            'quantity' => 'required|numeric|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
        ]);

        // التحقق من أن المنتج المختار هو منتج نهائي
        $item = Item::find($validated['item_id']);
        if ($item && $item->type !== 'finished_goods') {
            return back()->withErrors(['item_id' => 'يجب اختيار منتج نهائي للإنتاج.'])->withInput();
        }

        DB::transaction(function () use ($validated, $request) {
            // إنشاء أمر الإنتاج
            $productionOrder = ProductionOrder::create([
                'order_no' => $validated['order_no'],
                'item_id' => $validated['item_id'],
                'bill_of_material_id' => $validated['bill_of_material_id'],
                'work_center_id' => $validated['work_center_id'],
                'quantity' => $validated['quantity'],
                'order_date' => $validated['start_date'], // إضافة order_date
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'notes' => $validated['notes'],
                'status' => 'draft',
                'created_by' => $request->user()->id,
            ]);

            // إذا كان هناك قائمة مواد، إنشاء سجلات المواد المطلوبة
            if ($validated['bill_of_material_id']) {
                $bom = BillOfMaterial::with(['rawMaterial'])->find($validated['bill_of_material_id']);
                if ($bom && $bom->rawMaterial) {
                    $plannedQuantity = $bom->quantity * $validated['quantity'];
                    
                    ProductionMaterial::create([
                        'production_order_id' => $productionOrder->id,
                        'material_id' => $bom->raw_material_id,
                        'planned_quantity' => $plannedQuantity,
                        'unit_cost' => $bom->rawMaterial->standard_cost ?? 0,
                        'total_cost' => $plannedQuantity * ($bom->rawMaterial->standard_cost ?? 0),
                    ]);
                }
            }
        });

        return redirect()->route('production_orders.index')
            ->with('success', 'تم إنشاء أمر الإنتاج بنجاح.');
    }

    public function show($id)
    {
        $productionOrder = ProductionOrder::with(['item', 'billOfMaterial', 'workCenter', 'materials.material', 'creator'])->findOrFail($id);
        return view('production_orders.show', compact('productionOrder'));
    }

    public function edit($id)
    {
        $productionOrder = ProductionOrder::findOrFail($id);
        $boms = BillOfMaterial::with(['finishedGood', 'rawMaterial'])->get();
        $workCenters = WorkCenter::all();
        $finishedGoods = Item::where('type', 'finished_goods')->get();
        $productionOrder->load(['materials.material']);
        return view('production_orders.edit', compact('productionOrder', 'boms', 'workCenters', 'finishedGoods'));
    }

    public function update(Request $request, $id)
    {
        $productionOrder = ProductionOrder::findOrFail($id);
        
        $validated = $request->validate([
            'order_no' => 'required|unique:production_orders,order_no,' . $productionOrder->id,
            'item_id' => 'required|exists:items,id',
            'bill_of_material_id' => 'nullable|exists:bill_of_materials,id',
            'work_center_id' => 'nullable|exists:work_centers,id',
            'quantity' => 'required|numeric|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
        ]);

        $productionOrder->update([
            'order_no' => $validated['order_no'],
            'item_id' => $validated['item_id'],
            'bill_of_material_id' => $validated['bill_of_material_id'],
            'work_center_id' => $validated['work_center_id'],
            'quantity' => $validated['quantity'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('production_orders.index')
            ->with('success', 'تم تحديث أمر الإنتاج بنجاح.');
    }

    public function destroy($id)
    {
        $productionOrder = ProductionOrder::findOrFail($id);
        $productionOrder->delete();
        return redirect()->route('production_orders.index')
            ->with('success', 'تم حذف أمر الإنتاج بنجاح.');
    }

    public function activate($id)
    {
        $productionOrder = ProductionOrder::findOrFail($id);
        
        if ($productionOrder->status !== 'draft') {
            return redirect()->route('production_orders.show', $productionOrder->id)
                ->with('error', 'لا يمكن تفعيل أمر الإنتاج إلا إذا كان في حالة مسودة.');
        }

        $productionOrder->update(['status' => 'released']);
        
        return redirect()->route('production_orders.show', $productionOrder->id)
            ->with('success', 'تم تفعيل أمر الإنتاج بنجاح. يمكن الآن بدء الإنتاج.');
    }

    public function startProduction($id)
    {
        $productionOrder = ProductionOrder::with(['materials.material'])->findOrFail($id);
        
        if ($productionOrder->status !== 'released') {
            return redirect()->route('production_orders.show', $productionOrder->id)
                ->with('error', 'لا يمكن بدء الإنتاج إلا إذا كان الأمر في حالة مفعل.');
        }
        
        // التحقق من وجود مواد للإنتاج
        if ($productionOrder->materials->isEmpty()) {
            return redirect()->route('production_orders.show', $productionOrder->id)
                ->with('error', 'لا توجد مواد محددة للإنتاج. يرجى التأكد من وجود قائمة مواد (BOM) للأمر.');
        }
        
        try {
            DB::transaction(function () use ($productionOrder) {
                // التحقق من توفر المواد الخام
                foreach ($productionOrder->materials as $material) {
                    if (!$material->material) {
                        throw new \Exception("المادة غير موجودة في النظام");
                    }
                    
                    $availableStock = $this->getAvailableStock($material->material_id);
                    if ($availableStock < $material->planned_quantity) {
                        throw new \Exception("المواد الخام غير متوفرة: " . $material->material->name . 
                                           " (المتوفر: " . $availableStock . "، المطلوب: " . $material->planned_quantity . ")");
                    }
                    $this->issueMaterial($material);
                }
                
                $productionOrder->update(['status' => 'in_progress']);
            });
            
            return redirect()->route('production_orders.show', $productionOrder->id)
                ->with('success', 'تم بدء الإنتاج وخصم المواد الخام بنجاح.');
                
        } catch (\Exception $e) {
            return redirect()->route('production_orders.show', $productionOrder->id)
                ->with('error', 'خطأ في بدء الإنتاج: ' . $e->getMessage());
        }
    }

    public function completeProduction(Request $request, $id)
    {
        $productionOrder = ProductionOrder::findOrFail($id);
        
        $validated = $request->validate([
            'actual_quantity' => 'required|numeric|min:1',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        // التحقق من وجود المستودع
        $warehouse = \App\Models\Warehouse::find($validated['warehouse_id']);
        if (!$warehouse) {
            return redirect()->route('production_orders.show', $productionOrder->id)
                ->with('error', 'المستودع المحدد غير موجود.');
        }

        try {
            DB::transaction(function () use ($productionOrder, $validated) {
                // احسب التكلفة الفعلية للمواد المصروفة لهذا الأمر
                $issued = InventoryTransaction::where('reference_type', 'production_order')
                    ->where('reference_id', $productionOrder->id)
                    ->where('type', 'issue')
                    ->get();

                $totalIssuedCost = $issued->reduce(function ($carry, $t) {
                    return $carry + ((float)$t->quantity * (float)$t->unit_cost);
                }, 0.0);

                $actualQty = (float)$validated['actual_quantity'];
                if ($actualQty <= 0) {
                    throw new \Exception('لا يمكن إكمال الإنتاج بكمية فعلية صفرية');
                }

                $unitCostFg = $totalIssuedCost > 0 ? ($totalIssuedCost / $actualQty) : 0;

                // استلام المنتج النهائي بتكلفة فعلية محسوبة
                $this->receiveFinishedGood($productionOrder, $validated['actual_quantity'], $validated['warehouse_id'], $unitCostFg);

                // إنشاء قيد محاسبي لتحويل التكلفة من خام إلى تام
                $entry = JournalEntry::create([
                    'entry_date' => now()->toDateString(),
                    'reference_no' => 'JE-PROD-' . $productionOrder->order_no,
                    'description' => 'قيد إنتاج - أمر رقم: ' . $productionOrder->order_no,
                    'entry_type' => 'adjustment',
                    'reference_type' => 'production_order',
                    'reference_id' => $productionOrder->id,
                    'status' => 'draft',
                    'created_by' => auth()->id(),
                ]);

                // مدين مخزون تام (10500) بقيمة التكلفة الفعلية
                $entry->items()->create([
                    'account_id' => self::getInventoryFinishedGoodsAccountId(),
                    'debit' => $totalIssuedCost,
                    'credit' => 0,
                    'description' => 'تحويل تكلفة إلى منتج تام',
                ]);

                // دائن مخزون خام (10300) بنفس القيمة
                $entry->items()->create([
                    'account_id' => self::getInventoryAccountId(),
                    'debit' => 0,
                    'credit' => $totalIssuedCost,
                    'description' => 'تحويل تكلفة من مواد خام',
                ]);

                $entry->post();

                // تحديث حالة أمر الإنتاج
                $productionOrder->update([
                    'status' => 'completed',
                    'actual_quantity' => $validated['actual_quantity'],
                    'end_date' => now()->toDateString(),
                ]);
            });
            
            return redirect()->route('production_orders.show', $productionOrder->id)
                ->with('success', 'تم إكمال الإنتاج وإضافة المنتج النهائي إلى المخزون بنجاح.');
                
        } catch (\Exception $e) {
            return redirect()->route('production_orders.show', $productionOrder->id)
                ->with('error', 'خطأ في إكمال الإنتاج: ' . $e->getMessage());
        }
    }

    private function getAvailableStock($itemId)
    {
        try {
            $receipts = InventoryTransaction::where('item_id', $itemId)
                ->whereIn('type', ['receipt', 'transfer'])
                ->sum('quantity');
            
            $issues = InventoryTransaction::where('item_id', $itemId)
                ->whereIn('type', ['issue'])
                ->sum('quantity');
            
            return max(0, $receipts - $issues);
        } catch (\Exception $e) {
            \Log::error('خطأ في حساب المخزون المتاح للمادة: ' . $itemId, [
                'error' => $e->getMessage(),
                'item_id' => $itemId
            ]);
            return 0;
        }
    }

    private function issueMaterial($productionMaterial)
    {
        try {
            // الحصول على أول مستودع متاح
            $warehouse = \App\Models\Warehouse::first();
            if (!$warehouse) {
                throw new \Exception('لا يوجد مستودع متاح في النظام');
            }

            // التحقق من توفر المخزون
            $availableStock = $this->getAvailableStock($productionMaterial->material_id);
            if ($availableStock < $productionMaterial->planned_quantity) {
                throw new \Exception("المخزون غير كافي للمادة: " . $productionMaterial->material->name . 
                                   " (المتوفر: " . $availableStock . "، المطلوب: " . $productionMaterial->planned_quantity . ")");
            }

            InventoryTransaction::create([
                'item_id' => $productionMaterial->material_id,
                'warehouse_id' => $warehouse->id,
                'type' => 'issue',
                'quantity' => $productionMaterial->planned_quantity,
                'unit_cost' => $productionMaterial->unit_cost,
                'reference_no' => 'PROD-' . $productionMaterial->productionOrder->order_no . '-' . time(),
                'reference_type' => 'production_order',
                'reference_id' => $productionMaterial->production_order_id,
                'transaction_date' => now(),
                'description' => 'صرف مواد للإنتاج - أمر رقم: ' . $productionMaterial->productionOrder->order_no,
                'created_by' => auth()->id(),
            ]);

            // تحديث الكمية المستخدمة في ProductionMaterial
            $productionMaterial->update([
                'used_quantity' => $productionMaterial->planned_quantity,
                'actual_cost' => $productionMaterial->planned_quantity * $productionMaterial->unit_cost
            ]);

        } catch (\Exception $e) {
            \Log::error('خطأ في صرف المواد للإنتاج', [
                'error' => $e->getMessage(),
                'production_material_id' => $productionMaterial->id,
                'material_id' => $productionMaterial->material_id
            ]);
            throw new \Exception('فشل في صرف المواد: ' . $e->getMessage());
        }
    }

    private function receiveFinishedGood($productionOrder, $actualQuantity, $warehouseId, $unitCost)
    {
        InventoryTransaction::create([
            'item_id' => $productionOrder->item_id,
            'warehouse_id' => $warehouseId,
            'type' => 'receipt',
            'quantity' => $actualQuantity,
            'unit_cost' => $unitCost,
            'reference_no' => 'PROD-RCV-' . $productionOrder->order_no . '-' . time(),
            'reference_type' => 'production_order',
            'reference_id' => $productionOrder->id,
            'transaction_date' => now(),
            'description' => 'استلام منتج نهائي من الإنتاج - أمر رقم: ' . $productionOrder->order_no,
            'created_by' => auth()->id(),
        ]);
    }
} 