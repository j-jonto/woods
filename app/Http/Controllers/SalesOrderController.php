<?php
namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Item;
use App\Models\SalesOrderItem;
use App\Models\RepresentativeTransaction;
use App\Models\InventoryTransaction;
use App\Models\Warehouse;
use App\Models\Treasury;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends Controller
{
    public function index()
    {
        $orders = SalesOrder::with('customer')->orderByDesc('order_date')->paginate(20);
        return view('sales_orders.index', compact('orders'));
    }

    public function create()
    {
        $customers = Customer::all();
        $items = Item::where('type', 'finished_goods')->get();
        $products = $items;
        return view('sales_orders.create', compact('customers', 'items', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_no' => 'required|unique:sales_orders',
            'order_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'payment_type' => 'required|in:cash,credit',
            'representative_id' => 'nullable|exists:sales_representatives,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric',
            'items.*.unit_price' => 'required|numeric',
        ]);
        DB::transaction(function () use ($validated, $request) {
            $order = SalesOrder::create([
                'order_no' => $validated['order_no'],
                'order_date' => $validated['order_date'],
                'customer_id' => $validated['customer_id'],
                'status' => 'draft',
                'payment_type' => $validated['payment_type'],
                'representative_id' => $validated['representative_id'],
                'total_amount' => 0,
                'created_by' => $request->user()->id,
            ]);
            $total = 0;
            foreach ($validated['items'] as $item) {
                $amount = $item['quantity'] * $item['unit_price'];
                SalesOrderItem::create([
                    'sales_order_id' => $order->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'amount' => $amount,
                ]);
                $total += $amount;
            }
            $order->update(['total_amount' => $total]);
            
            // تسجيل حركة بضاعة مستلمة للمندوب إذا كان محددًا
            if ($order->representative_id) {
                RepresentativeTransaction::create([
                    'representative_id' => $order->representative_id,
                    'type' => 'goods_received',
                    'amount' => $total,
                    'transaction_date' => $order->order_date,
                    'reference' => 'فاتورة مبيعات رقم ' . $order->order_no,
                    'notes' => 'بضاعة مستلمة من الشركة',
                    'created_by' => $request->user()->id,
                ]);
            }
        });
        return redirect()->route('sales_orders.index')->with('success', 'Sales order created.');
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'items.item']);
        return view('sales_orders.show', compact('salesOrder'));
    }

    public function edit(SalesOrder $salesOrder)
    {
        $customers = Customer::all();
        $items = Item::where('type', 'finished_goods')->get();
        $salesOrder->load('items');
        return view('sales_orders.edit', compact('salesOrder', 'customers', 'items'));
    }

    public function update(Request $request, SalesOrder $salesOrder)
    {
        $validated = $request->validate([
            'order_no' => 'required|unique:sales_orders,order_no,' . $salesOrder->id,
            'order_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'payment_type' => 'required|in:cash,credit',
            'representative_id' => 'nullable|exists:sales_representatives,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric',
            'items.*.unit_price' => 'required|numeric',
        ]);
        DB::transaction(function () use ($validated, $salesOrder) {
            $oldRepresentativeId = $salesOrder->representative_id;
            $salesOrder->update([
                'order_no' => $validated['order_no'],
                'order_date' => $validated['order_date'],
                'customer_id' => $validated['customer_id'],
                'payment_type' => $validated['payment_type'],
                'representative_id' => $validated['representative_id'],
            ]);
            $salesOrder->items()->delete();
            $total = 0;
            foreach ($validated['items'] as $item) {
                $amount = $item['quantity'] * $item['unit_price'];
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'amount' => $amount,
                ]);
                $total += $amount;
            }
            $salesOrder->update(['total_amount' => $total]);
            
            // تحديث حركة المندوب إذا تغير
            if ($oldRepresentativeId != $salesOrder->representative_id) {
                // حذف الحركة القديمة إذا وجدت
                if ($oldRepresentativeId) {
                    RepresentativeTransaction::where('representative_id', $oldRepresentativeId)
                        ->where('reference', 'فاتورة مبيعات رقم ' . $salesOrder->order_no)
                        ->delete();
                }
                
                // إضافة الحركة الجديدة إذا كان هناك مندوب
                if ($salesOrder->representative_id) {
                    RepresentativeTransaction::create([
                        'representative_id' => $salesOrder->representative_id,
                        'type' => 'goods_received',
                        'amount' => $total,
                        'transaction_date' => $salesOrder->order_date,
                        'reference' => 'فاتورة مبيعات رقم ' . $salesOrder->order_no,
                        'notes' => 'بضاعة مستلمة من الشركة',
                        'created_by' => auth()->id(),
                    ]);
                }
            }
        });
        return redirect()->route('sales_orders.index')->with('success', 'Sales order updated.');
    }

    public function destroy(SalesOrder $salesOrder)
    {
        $salesOrder->delete();
        return redirect()->route('sales_orders.index')->with('success', 'Sales order deleted.');
    }

    public function updateStatus(Request $request, SalesOrder $salesOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,pending,confirmed,shipped,delivered,cancelled'
        ]);

        $oldStatus = $salesOrder->status;
        $newStatus = $validated['status'];

        try {
            DB::transaction(function () use ($salesOrder, $newStatus, $oldStatus) {
                $salesOrder->update(['status' => $newStatus]);

                // إذا تم تأكيد الطلب أو شحنه، خصم من المخزون
                if (in_array($newStatus, ['confirmed', 'shipped', 'delivered']) && 
                    !in_array($oldStatus, ['confirmed', 'shipped', 'delivered'])) {
                    $this->issueInventoryForSalesOrder($salesOrder);
                }

                // إذا تم إلغاء الطلب وكان قد تم تأكيده مسبقاً، إعادة المخزون
                if ($newStatus === 'cancelled' && in_array($oldStatus, ['confirmed', 'shipped', 'delivered'])) {
                    $this->returnInventoryForSalesOrder($salesOrder);
                }
            });

            $statusMessages = [
                'draft' => 'تم حفظ الطلب كمسودة',
                'pending' => 'تم تأكيد الطلب',
                'confirmed' => 'تم تأكيد الطلب وخصم من المخزون',
                'shipped' => 'تم شحن الطلب',
                'delivered' => 'تم تسليم الطلب',
                'cancelled' => 'تم إلغاء الطلب وإعادة المخزون'
            ];

            return redirect()->route('sales_orders.show', $salesOrder)
                ->with('success', $statusMessages[$newStatus] ?? 'تم تحديث حالة الطلب');

        } catch (\Exception $e) {
            return redirect()->route('sales_orders.show', $salesOrder)
                ->with('error', 'خطأ في تحديث حالة الطلب: ' . $e->getMessage());
        }
    }

    private function issueInventoryForSalesOrder($salesOrder)
    {
        $warehouse = Warehouse::first();
        if (!$warehouse) {
            throw new \Exception('لا يوجد مستودع متاح في النظام');
        }

        $salesOrder->load('items.item');
        
        foreach ($salesOrder->items as $orderItem) {
            // التحقق من توفر المخزون
            $availableStock = $this->getAvailableStock($orderItem->item_id);
            if ($availableStock < $orderItem->quantity) {
                throw new \Exception("المخزون غير متوفر للمنتج: " . $orderItem->item->name . 
                                   " (المتوفر: " . $availableStock . "، المطلوب: " . $orderItem->quantity . ")");
            }

            InventoryTransaction::create([
                'transaction_date' => now(),
                'reference_no' => 'SO-' . $salesOrder->order_no . '-' . $orderItem->id,
                'type' => 'issue',
                'item_id' => $orderItem->item_id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $orderItem->quantity,
                'unit_cost' => $orderItem->item->standard_cost ?? 0,
                'reference_type' => 'sales_order',
                'reference_id' => $salesOrder->id,
                'description' => 'صرف بضاعة لطلب بيع: ' . $salesOrder->order_no,
                'created_by' => auth()->id(),
            ]);
        }

        // ربط المبيعات النقدية بالخزنة
        if ($salesOrder->payment_type == 'cash') {
            $this->addSalesToTreasury($salesOrder);
        } else {
            // ربط المبيعات الآجلة بحساب العميل
            $this->addSalesToCustomerAccount($salesOrder);
        }

        // حساب العمولات تلقائياً
        if ($salesOrder->representative_id) {
            $this->calculateCommission($salesOrder);
        }

        // إنشاء قيد محاسبي تلقائياً
        $this->createJournalEntry($salesOrder);
    }

    private function addSalesToTreasury($salesOrder)
    {
        $treasury = Treasury::first();
        if (!$treasury) {
            throw new \Exception('لا توجد خزنة متاحة في النظام');
        }

        try {
            $treasury->addReceipt(
                $salesOrder->total_amount,
                'مبيعات نقدية - طلب رقم: ' . $salesOrder->order_no,
                'sales_order',
                $salesOrder->id
            );
        } catch (\Exception $e) {
            \Log::error('خطأ في إضافة المبيعات للخزنة', [
                'error' => $e->getMessage(),
                'sales_order_id' => $salesOrder->id,
                'amount' => $salesOrder->total_amount
            ]);
            throw new \Exception('فشل في إضافة المبيعات للخزنة: ' . $e->getMessage());
        }
    }

    private function returnInventoryForSalesOrder($salesOrder)
    {
        $warehouse = Warehouse::first();
        if (!$warehouse) {
            throw new \Exception('لا يوجد مستودع متاح في النظام');
        }

        $salesOrder->load('items.item');
        
        foreach ($salesOrder->items as $orderItem) {
            InventoryTransaction::create([
                'transaction_date' => now(),
                'reference_no' => 'SO-RET-' . $salesOrder->order_no . '-' . $orderItem->id,
                'type' => 'receipt',
                'item_id' => $orderItem->item_id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $orderItem->quantity,
                'unit_cost' => $orderItem->item->standard_cost ?? 0,
                'reference_type' => 'sales_order',
                'reference_id' => $salesOrder->id,
                'description' => 'إعادة بضاعة من طلب بيع ملغي: ' . $salesOrder->order_no,
                'created_by' => auth()->id(),
            ]);
        }

        // إزالة المبيعات النقدية من الخزنة عند الإلغاء
        if ($salesOrder->payment_type == 'cash') {
            $this->removeSalesFromTreasury($salesOrder);
        } else {
            // إزالة المبيعات الآجلة من حساب العميل
            $this->removeSalesFromCustomerAccount($salesOrder);
        }
    }

    private function removeSalesFromTreasury($salesOrder)
    {
        $treasury = Treasury::first();
        if (!$treasury) {
            throw new \Exception('لا توجد خزنة متاحة في النظام');
        }

        try {
            $treasury->addPayment(
                $salesOrder->total_amount,
                'إلغاء مبيعات نقدية - طلب رقم: ' . $salesOrder->order_no,
                'sales_order',
                $salesOrder->id
            );
        } catch (\Exception $e) {
            \Log::error('خطأ في إزالة المبيعات من الخزنة', [
                'error' => $e->getMessage(),
                'sales_order_id' => $salesOrder->id,
                'amount' => $salesOrder->total_amount
            ]);
            throw new \Exception('فشل في إزالة المبيعات من الخزنة: ' . $e->getMessage());
        }
    }

    private function calculateCommission($salesOrder)
    {
        try {
            $representative = $salesOrder->representative;
            $commission = $salesOrder->total_amount * ($representative->commission_rate / 100);
            
            RepresentativeTransaction::create([
                'representative_id' => $representative->id,
                'type' => 'commission',
                'amount' => $commission,
                'transaction_date' => now(),
                'reference' => 'عمولة مبيعات - طلب رقم: ' . $salesOrder->order_no,
                'notes' => 'عمولة مبيعات بنسبة ' . $representative->commission_rate . '%',
                'created_by' => auth()->id(),
            ]);

            // خصم العمولة من الخزنة
            $treasury = Treasury::first();
            if ($treasury) {
                $treasury->addPayment(
                    $commission,
                    'عمولة مندوب: ' . $representative->name . ' - طلب رقم: ' . $salesOrder->order_no,
                    'representative_commission',
                    $representative->id
                );
            }

        } catch (\Exception $e) {
            \Log::error('خطأ في حساب العمولة', [
                'error' => $e->getMessage(),
                'sales_order_id' => $salesOrder->id,
                'representative_id' => $salesOrder->representative_id
            ]);
            throw new \Exception('فشل في حساب العمولة: ' . $e->getMessage());
        }
    }

    private function addSalesToCustomerAccount($salesOrder)
    {
        try {
            $customer = $salesOrder->customer;
            if (!$customer) {
                throw new \Exception('العميل غير موجود');
            }

            // التحقق من الحد الائتماني
            $summary = $customer->getSummary();
            if ($summary['is_over_limit']) {
                throw new \Exception('العميل تجاوز الحد الائتماني المسموح');
            }

            $customer->addReceivable(
                $salesOrder->total_amount,
                'مبيعات آجلة - طلب رقم: ' . $salesOrder->order_no,
                'sales_order',
                $salesOrder->id
            );

        } catch (\Exception $e) {
            \Log::error('خطأ في إضافة المبيعات لحساب العميل', [
                'error' => $e->getMessage(),
                'sales_order_id' => $salesOrder->id,
                'customer_id' => $salesOrder->customer_id
            ]);
            throw new \Exception('فشل في إضافة المبيعات لحساب العميل: ' . $e->getMessage());
        }
    }

    private function removeSalesFromCustomerAccount($salesOrder)
    {
        try {
            $customer = $salesOrder->customer;
            if (!$customer) {
                throw new \Exception('العميل غير موجود');
            }

            $customer->addPayment(
                $salesOrder->total_amount,
                'إلغاء مبيعات آجلة - طلب رقم: ' . $salesOrder->order_no,
                'sales_order',
                $salesOrder->id
            );

        } catch (\Exception $e) {
            \Log::error('خطأ في إزالة المبيعات من حساب العميل', [
                'error' => $e->getMessage(),
                'sales_order_id' => $salesOrder->id,
                'customer_id' => $salesOrder->customer_id
            ]);
            throw new \Exception('فشل في إزالة المبيعات من حساب العميل: ' . $e->getMessage());
        }
    }

    private function createJournalEntry($salesOrder)
    {
        try {
            JournalEntry::createSalesEntry($salesOrder);
        } catch (\Exception $e) {
            \Log::error('خطأ في إنشاء القيد المحاسبي للمبيعات', [
                'error' => $e->getMessage(),
                'sales_order_id' => $salesOrder->id
            ]);
            throw new \Exception('فشل في إنشاء القيد المحاسبي: ' . $e->getMessage());
        }
    }

    private function getAvailableStock($itemId)
    {
        try {
            $receipts = InventoryTransaction::where('item_id', $itemId)
                ->whereIn('type', ['receipt', 'transfer'])
                ->sum('quantity');
            
            $issues = InventoryTransaction::where('item_id', $itemId)
                ->whereIn('type', ['issue', 'sale'])
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
} 