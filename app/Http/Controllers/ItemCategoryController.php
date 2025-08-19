<?php
namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Illuminate\Http\Request;

class ItemCategoryController extends Controller
{
    public function index()
    {
        $categories = ItemCategory::paginate(20);
        return view('item_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('item_categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:item_categories',
        ]);
        ItemCategory::create($validated + [
            'description' => $request->input('description'),
        ]);
        return redirect()->route('item_categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(ItemCategory $itemCategory)
    {
        return view('item_categories.edit', compact('itemCategory'));
    }

    public function update(Request $request, ItemCategory $itemCategory)
    {
        $validated = $request->validate([
            'name' => 'required|unique:item_categories,name,' . $itemCategory->id,
        ]);
        $itemCategory->update($validated + [
            'description' => $request->input('description'),
        ]);
        return redirect()->route('item_categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(ItemCategory $itemCategory)
    {
        $itemCategory->delete();
        return redirect()->route('item_categories.index')->with('success', 'Category deleted successfully.');
    }
} 