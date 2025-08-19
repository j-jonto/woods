<?php
namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryTransactionController extends Controller
{
    public function index()
    {
        $transactions = InventoryTransaction::with(['item', 'warehouse'])->orderByDesc('transaction_date')->paginate(20);
        return view('inventory_transactions.index', compact('transactions'));
    }

    public function create()
    {
        $items = Item::all();
        $warehouses = Warehouse::all();
        return view('inventory_transactions.create', compact('items', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'reference_no' => 'required|unique:inventory_transactions',
            'type' => 'required',
            'item_id' => 'required|exists:items,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric',
            'unit_cost' => 'nullable|numeric',
        ]);
        DB::transaction(function () use ($validated, $request) {
            InventoryTransaction::create($validated + [
                'batch_no' => $request->input('batch_no'),
                'reference_type' => $request->input('reference_type'),
                'reference_id' => $request->input('reference_id'),
                'description' => $request->input('description'),
                'created_by' => $request->user()->id,
            ]);
        });
        return redirect()->route('inventory_transactions.index')->with('success', 'Inventory transaction created.');
    }

    public function show(InventoryTransaction $inventoryTransaction)
    {
        $inventoryTransaction->load(['item', 'warehouse']);
        return view('inventory_transactions.show', compact('inventoryTransaction'));
    }

    public function destroy(InventoryTransaction $inventoryTransaction)
    {
        $inventoryTransaction->delete();
        return redirect()->route('inventory_transactions.index')->with('success', 'Transaction deleted.');
    }
} 