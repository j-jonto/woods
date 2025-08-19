<?php
namespace App\Http\Controllers;

use App\Models\BillOfMaterial;
use App\Models\Item;
use Illuminate\Http\Request;

class BillOfMaterialController extends Controller
{
    public function index()
    {
        $boms = BillOfMaterial::with(['finishedGood', 'rawMaterial'])->paginate(20);
        return view('bill_of_materials.index', compact('boms'));
    }

    public function create()
    {
        $finishedGoods = Item::where('type', 'finished_goods')->get();
        $rawMaterials = Item::where('type', 'raw_material')->get();
        return view('bill_of_materials.create', compact('finishedGoods', 'rawMaterials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'finished_good_id' => 'required|exists:items,id',
            'raw_material_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $bom = BillOfMaterial::create([
            'finished_good_id' => $validated['finished_good_id'],
            'raw_material_id' => $validated['raw_material_id'],
            'quantity' => $validated['quantity'],
            'description' => $request->input('description'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('boms.index')
            ->with('success', 'تم إنشاء قائمة المواد بنجاح.');
    }

    public function show($id)
    {
        $billOfMaterial = BillOfMaterial::with(['finishedGood', 'rawMaterial'])->findOrFail($id);
        return view('bill_of_materials.show', compact('billOfMaterial'));
    }

    public function edit($id)
    {
        $billOfMaterial = BillOfMaterial::findOrFail($id);
        $finishedGoods = Item::where('type', 'finished_goods')->get();
        $rawMaterials = Item::where('type', 'raw_material')->get();
        return view('bill_of_materials.edit', compact('billOfMaterial', 'finishedGoods', 'rawMaterials'));
    }

    public function update(Request $request, $id)
    {
        $billOfMaterial = BillOfMaterial::findOrFail($id);
        
        $validated = $request->validate([
            'finished_good_id' => 'required|exists:items,id',
            'raw_material_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $billOfMaterial->update([
            'finished_good_id' => $validated['finished_good_id'],
            'raw_material_id' => $validated['raw_material_id'],
            'quantity' => $validated['quantity'],
            'description' => $request->input('description'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('boms.index')
            ->with('success', 'تم تحديث قائمة المواد بنجاح.');
    }

    public function destroy($id)
    {
        $billOfMaterial = BillOfMaterial::findOrFail($id);
        $billOfMaterial->delete();
        return redirect()->route('boms.index')
            ->with('success', 'تم حذف قائمة المواد بنجاح.');
    }
} 