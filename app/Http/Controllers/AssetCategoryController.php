<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use Illuminate\Http\Request;

class AssetCategoryController extends Controller
{
    public function index()
    {
        $categories = AssetCategory::orderBy('name')->paginate(20);
        return view('asset_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('asset_categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'description' => 'nullable|string',
        ]);
        AssetCategory::create($validated);
        return redirect()->route('asset_categories.index')->with('success', 'تم إنشاء التصنيف بنجاح');
    }

    public function show(AssetCategory $assetCategory)
    {
        return view('asset_categories.show', compact('assetCategory'));
    }

    public function edit(AssetCategory $assetCategory)
    {
        return view('asset_categories.edit', compact('assetCategory'));
    }

    public function update(Request $request, AssetCategory $assetCategory)
    {
        $validated = $request->validate([
            'name' => 'required',
            'description' => 'nullable|string',
        ]);
        $assetCategory->update($validated);
        return redirect()->route('asset_categories.index')->with('success', 'تم تحديث التصنيف بنجاح');
    }

    public function destroy(AssetCategory $assetCategory)
    {
        $assetCategory->delete();
        return redirect()->route('asset_categories.index')->with('success', 'تم حذف التصنيف بنجاح');
    }
} 