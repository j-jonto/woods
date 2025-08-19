<?php
namespace App\Http\Controllers;

use App\Models\FixedAsset;
use Illuminate\Http\Request;

class FixedAssetController extends Controller
{
    public function index()
    {
        $assets = FixedAsset::paginate(20);
        return view('fixed_assets.index', compact('assets'));
    }

    public function create()
    {
        return view('fixed_assets.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:fixed_assets',
            'name' => 'required',
            'acquisition_date' => 'required|date',
            'acquisition_cost' => 'required|numeric',
            'useful_life' => 'required|integer',
            'depreciation_method' => 'required',
        ]);
        FixedAsset::create($validated + [
            'description' => $request->input('description'),
            'status' => $request->input('status', 'active'),
        ]);
        return redirect()->route('fixed_assets.index')->with('success', 'Asset created successfully.');
    }

    public function edit(FixedAsset $fixedAsset)
    {
        return view('fixed_assets.edit', compact('fixedAsset'));
    }

    public function update(Request $request, FixedAsset $fixedAsset)
    {
        $validated = $request->validate([
            'code' => 'required|unique:fixed_assets,code,' . $fixedAsset->id,
            'name' => 'required',
            'acquisition_date' => 'required|date',
            'acquisition_cost' => 'required|numeric',
            'useful_life' => 'required|integer',
            'depreciation_method' => 'required',
        ]);
        $fixedAsset->update($validated + [
            'description' => $request->input('description'),
            'status' => $request->input('status', 'active'),
        ]);
        return redirect()->route('fixed_assets.index')->with('success', 'Asset updated successfully.');
    }

    public function destroy(FixedAsset $fixedAsset)
    {
        $fixedAsset->delete();
        return redirect()->route('fixed_assets.index')->with('success', 'Asset deleted successfully.');
    }

    public function dispose(FixedAsset $fixedAsset)
    {
        $fixedAsset->status = 'disposed';
        $fixedAsset->save();
        return redirect()->route('fixed_assets.index')->with('success', 'Asset disposed successfully.');
    }

    public function show(FixedAsset $fixedAsset)
    {
        return view('fixed_assets.show', compact('fixedAsset'));
    }
} 