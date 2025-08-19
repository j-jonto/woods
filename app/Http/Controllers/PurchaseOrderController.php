<?php
namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\PurchaseOrderItem;
use App\Models\InventoryTransaction;
use App\Models\SupplierPayment;
use App\Models\Treasury;
use App\Models\Warehouse;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::with('supplier')->orderByDesc('order_date')->paginate(20);
        return view('purchase_orders.index', compact('orders'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $items = Item::all();
        $products = $items;
        return view('purchase_orders.create', compact('suppliers', 'items', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_no' => 'required|unique:purchase_orders',
            'order_date' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_type' => 'required|in:cash,credit',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric',
            'items.*.unit_price' => 'required|numeric',
        ]);
        DB::transaction(function () use ($validated, $request) {
            $order = PurchaseOrder::create([
                'order_no' => $validated['order_no'],
                'order_date' => $validated['order_date'],
                'supplier_id' => $validated['supplier_id'],
                'status' => 'draft',
                'payment_type' => $validated['payment_type'],
                'total_amount' => 0,
                'created_by' => $request->user()->id,
            ]);
            $total = 0;
            foreach ($validated['items'] as $item) {
                $amount = $item['quantity'] * $item['unit_price'];
                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'amount' => $amount,
                ]);
                $total += $amount;
            }
            $order->update(['total_amount' => $total]);
        });
        return redirect()->route('purchase_orders.index')->with('success', 'Purchase order created.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.item']);
        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $suppliers = Supplier::all();
        $items = Item::all();
        $purchaseOrder->load('items');
        return view('purchase_orders.edit', compact('purchaseOrder', 'suppliers', 'items'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'order_no' => 'required|unique:purchase_orders,order_no,' . $purchaseOrder->id,
            'order_date' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_type' => 'required|in:cash,credit',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric',
            'items.*.unit_price' => 'required|numeric',
        ]);
        DB::transaction(function () use ($validated, $purchaseOrder) {
            $purchaseOrder->update([
                'order_no' => $validated['order_no'],
                'order_date' => $validated['order_date'],
                'supplier_id' => $validated['supplier_id'],
                'payment_type' => $validated['payment_type'],
            ]);
            $purchaseOrder->items()->delete();
            $total = 0;
            foreach ($validated['items'] as $item) {
                $amount = $item['quantity'] * $item['unit_price'];
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'amount' => $amount,
                ]);
                $total += $amount;
            }
            $purchaseOrder->update(['total_amount' => $total]);
        });
        return redirect()->route('purchase_orders.index')->with('success', 'Purchase order updated.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();
        return redirect()->route('purchase_orders.index')->with('success', 'Purchase order deleted.');
    }

    public function receive($id)
    {
        $purchaseOrder = PurchaseOrder::with(['items.item'])->findOrFail($id);
        
        if ($purchaseOrder->status !== 'ordered') {
            return redirect()->route('purchase_orders.show', $purchaseOrder->id)
                ->with('error', 'لا يمكن استلام البضاعة إلا إذا كان الأمر في حالة "مطلوب".');
        }

        try {
            DB::transaction(function () use ($purchaseOrder) {
                // تحديث حالة أمر الشراء
                $purchaseOrder->update(['status' => 'received']);
                
                // الحصول على أول مستودع متاح
                $warehouse = Warehouse::first();
                if (!$warehouse) {
                    throw new \Exception('لا يوجد مستودع متاح في النظام');
                }
                
                // إنشاء معاملات مخزون تلقائياً
                foreach ($purchaseOrder->items as $item) {
                    InventoryTransaction::create([
                        'transaction_date' => now(),
                        'reference_no' => 'PO-' . $purchaseOrder->order_no . '-' . $item->id . '-' . time(),
                        'type' => 'receipt',
                        'item_id' => $item->item_id,
                        'warehouse_id' => $warehouse->id,
                        'quantity' => $item->quantity,
                        'unit_cost' => $item->unit_price,
                        'reference_type' => 'purchase_order',
                        'reference_id' => $purchaseOrder->id,
                        'description' => 'استلام بضاعة من أمر شراء: ' . $purchaseOrder->order_no,
                        'created_by' => auth()->id(),
                    ]);
                }
                
                // إنشاء التزام دفع إذا كان الدفع آجل
                if ($purchaseOrder->payment_type == 'credit') {
                    $this->addPurchaseToSupplierAccount($purchaseOrder);
                } else {
                    // إذا كان الدفع نقدي، خصم من الخزنة العامة فوراً
                    $this->deductPurchaseFromTreasury($purchaseOrder);
                }

                // إنشاء قيد محاسبي تلقائياً
                $this->createJournalEntry($purchaseOrder);
            });
            
            $message = 'تم استلام البضاعة وتحديث المخزون بنجاح.';
            if ($purchaseOrder->payment_type == 'credit') {
                $message .= ' تم إنشاء التزام دفع آجل.';
            } else {
                $message .= ' تم خصم المبلغ من الخزنة العامة.';
            }
            
            return redirect()->route('purchase_orders.show', $purchaseOrder->id)
                ->with('success', $message);
                
        } catch (\Exception $e) {
            return redirect()->route('purchase_orders.show', $purchaseOrder->id)
                ->with('error', 'حدث خطأ أثناء استلام البضاعة: ' . $e->getMessage());
        }
    }

    public function confirm($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('purchase_orders.show', $purchaseOrder->id)
                ->with('error', 'لا يمكن تأكيد الأمر إلا إذا كان في حالة مسودة.');
        }

        $purchaseOrder->update(['status' => 'ordered']);
        
        return redirect()->route('purchase_orders.show', $purchaseOrder->id)
            ->with('success', 'تم تأكيد أمر الشراء بنجاح.');
    }

    private function deductPurchaseFromTreasury($purchaseOrder)
    {
        $treasury = Treasury::first();
        if (!$treasury) {
            throw new \Exception('لا توجد خزنة متاحة في النظام');
        }

        try {
            $treasury->addPayment(
                $purchaseOrder->total_amount,
                'دفع نقدي للمورد: ' . $purchaseOrder->supplier->name . ' - أمر شراء: ' . $purchaseOrder->order_no,
                'purchase_order',
                $purchaseOrder->id
            );
        } catch (\Exception $e) {
            \Log::error('خطأ في خصم المشتريات من الخزنة', [
                'error' => $e->getMessage(),
                'purchase_order_id' => $purchaseOrder->id,
                'amount' => $purchaseOrder->total_amount
            ]);
            throw new \Exception('فشل في خصم المشتريات من الخزنة: ' . $e->getMessage());
        }
    }

    private function addPurchaseToSupplierAccount($purchaseOrder)
    {
        try {
            $supplier = $purchaseOrder->supplier;
            if (!$supplier) {
                throw new \Exception('المورد غير موجود');
            }

            $supplier->addPayable(
                $purchaseOrder->total_amount,
                'مشتريات آجلة - أمر شراء: ' . $purchaseOrder->order_no,
                'purchase_order',
                $purchaseOrder->id
            );

        } catch (\Exception $e) {
            \Log::error('خطأ في إضافة المشتريات لحساب المورد', [
                'error' => $e->getMessage(),
                'purchase_order_id' => $purchaseOrder->id,
                'supplier_id' => $purchaseOrder->supplier_id
            ]);
            throw new \Exception('فشل في إضافة المشتريات لحساب المورد: ' . $e->getMessage());
        }
    }

    private function createJournalEntry($purchaseOrder)
    {
        try {
            JournalEntry::createPurchaseEntry($purchaseOrder);
        } catch (\Exception $e) {
            \Log::error('خطأ في إنشاء القيد المحاسبي للمشتريات', [
                'error' => $e->getMessage(),
                'purchase_order_id' => $purchaseOrder->id
            ]);
            throw new \Exception('فشل في إنشاء القيد المحاسبي: ' . $e->getMessage());
        }
    }
} 