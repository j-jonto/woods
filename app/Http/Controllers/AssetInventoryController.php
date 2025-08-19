<?php

namespace App\Http\Controllers;

use App\Models\AssetInventory;
use App\Models\FixedAsset;
use Illuminate\Http\Request;

class AssetInventoryController extends Controller
{
    public function index()
    {
        $inventories = AssetInventory::with('asset')->orderByDesc('inventory_date')->paginate(20);
        return view('asset_inventories.index', compact('inventories'));
    }

    public function create()
    {
        $assets = FixedAsset::all();
        return view('asset_inventories.create', compact('assets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:fixed_assets,id',
            'location' => 'nullable|string',
            'status' => 'nullable|string',
            'inventory_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);
        AssetInventory::create($validated);
        return redirect()->route('asset_inventories.index')->with('success', 'تم تسجيل الجرد بنجاح');
    }

    public function show(AssetInventory $assetInventory)
    {
        $assetInventory->load('asset');
        return view('asset_inventories.show', compact('assetInventory'));
    }

    public function edit(AssetInventory $assetInventory)
    {
        $assets = FixedAsset::all();
        return view('asset_inventories.edit', compact('assetInventory', 'assets'));
    }

    public function update(Request $request, AssetInventory $assetInventory)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:fixed_assets,id',
            'location' => 'nullable|string',
            'status' => 'nullable|string',
            'inventory_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);
        $assetInventory->update($validated);
        return redirect()->route('asset_inventories.index')->with('success', 'تم تحديث الجرد بنجاح');
    }

    public function destroy(AssetInventory $assetInventory)
    {
        $assetInventory->delete();
        return redirect()->route('asset_inventories.index')->with('success', 'تم حذف الجرد بنجاح');
    }
} 