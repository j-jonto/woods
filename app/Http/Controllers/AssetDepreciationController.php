<?php

namespace App\Http\Controllers;

use App\Models\AssetDepreciation;
use App\Models\FixedAsset;
use Illuminate\Http\Request;

class AssetDepreciationController extends Controller
{
    public function index()
    {
        $depreciations = AssetDepreciation::with('asset')->orderByDesc('date')->paginate(20);
        return view('asset_depreciations.index', compact('depreciations'));
    }

    public function create()
    {
        $assets = FixedAsset::all();
        return view('asset_depreciations.create', compact('assets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:fixed_assets,id',
            'date' => 'required|date',
            'amount' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);
        AssetDepreciation::create($validated);
        return redirect()->route('asset_depreciations.index')->with('success', 'تم تسجيل الإهلاك بنجاح');
    }

    public function show(AssetDepreciation $assetDepreciation)
    {
        $assetDepreciation->load('asset');
        return view('asset_depreciations.show', compact('assetDepreciation'));
    }

    public function edit(AssetDepreciation $assetDepreciation)
    {
        $assets = FixedAsset::all();
        return view('asset_depreciations.edit', compact('assetDepreciation', 'assets'));
    }

    public function update(Request $request, AssetDepreciation $assetDepreciation)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:fixed_assets,id',
            'date' => 'required|date',
            'amount' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);
        $assetDepreciation->update($validated);
        return redirect()->route('asset_depreciations.index')->with('success', 'تم تحديث الإهلاك بنجاح');
    }

    public function destroy(AssetDepreciation $assetDepreciation)
    {
        $assetDepreciation->delete();
        return redirect()->route('asset_depreciations.index')->with('success', 'تم حذف الإهلاك بنجاح');
    }
} 