<?php
namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with('category')->paginate(20);
        return view('items.index', compact('items'));
    }

    public function create()
    {
        $categories = ItemCategory::all();
        return view('items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:items',
            'barcode' => 'nullable|unique:items',
            'name' => 'required',
            'type' => 'required',
            'unit_of_measure' => 'required',
            'category_id' => 'nullable|exists:item_categories,id',
        ]);
        $item = Item::create($validated + [
            'description' => $request->input('description'),
            'standard_cost' => $request->input('standard_cost', 0),
            'selling_price' => $request->input('selling_price', 0),
            'reorder_point' => $request->input('reorder_point', 0),
            'reorder_quantity' => $request->input('reorder_quantity', 0),
            'is_active' => $request->input('is_active', true),
        ]);
        // سجل المراجعة - إضافة
        AuditLog::create([
            'user_id' => $request->user()->id ?? null,
            'action' => 'create',
            'table_name' => 'items',
            'model_id' => $item->id,
            'model_type' => \App\Models\Item::class,
            'old_values' => null,
            'new_values' => json_encode($item->toArray(), JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);
        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }

    public function show(Item $item)
    {
        $item->load(['category', 'inventoryTransactions.warehouse']);
        return view('items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $categories = ItemCategory::all();
        return view('items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'code' => 'required|unique:items,code,' . $item->id,
            'barcode' => 'nullable|unique:items,barcode,' . $item->id,
            'name' => 'required',
            'type' => 'required',
            'unit_of_measure' => 'required',
            'category_id' => 'nullable|exists:item_categories,id',
        ]);
        $old = $item->getOriginal();
        $item->update($validated + [
            'description' => $request->input('description'),
            'standard_cost' => $request->input('standard_cost', 0),
            'selling_price' => $request->input('selling_price', 0),
            'reorder_point' => $request->input('reorder_point', 0),
            'reorder_quantity' => $request->input('reorder_quantity', 0),
            'is_active' => $request->input('is_active', true),
        ]);
        // سجل المراجعة - تعديل
        AuditLog::create([
            'user_id' => $request->user()->id ?? null,
            'action' => 'update',
            'table_name' => 'items',
            'model_id' => $item->id,
            'model_type' => \App\Models\Item::class,
            'old_values' => json_encode($old, JSON_UNESCAPED_UNICODE),
            'new_values' => json_encode($item->toArray(), JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);
        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        try {
            // التحقق من إمكانية الحذف
            if (!$item->canBeDeleted()) {
                return redirect()->route('items.index')
                    ->with('error', 'لا يمكن حذف هذا المنتج: ' . $item->getDeletionErrorMessage());
            }

            $old = $item->toArray();
            $id = $item->id;
            
            // استخدام الحذف الآمن
            $item->safeDelete();
            
            // سجل المراجعة - حذف
            AuditLog::create([
                'user_id' => auth()->user()->id ?? null,
                'action' => 'delete',
                'table_name' => 'items',
                'model_id' => $id,
                'model_type' => \App\Models\Item::class,
                'old_values' => json_encode($old, JSON_UNESCAPED_UNICODE),
                'new_values' => null,
                'created_at' => now(),
            ]);
            
            return redirect()->route('items.index')->with('success', 'تم حذف المنتج بنجاح.');
            
        } catch (\Exception $e) {
            return redirect()->route('items.index')
                ->with('error', 'حدث خطأ أثناء حذف المنتج: ' . $e->getMessage());
        }
    }
} 