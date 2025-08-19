<?php

namespace App\Http\Controllers;

use App\Models\RevenueType;
use Illuminate\Http\Request;

class RevenueTypeController extends Controller
{
    public function index()
    {
        $types = RevenueType::orderBy('name')->paginate(20);
        return view('revenue_types.index', compact('types'));
    }

    public function create()
    {
        return view('revenue_types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'description' => 'nullable|string',
        ]);
        RevenueType::create($validated);
        return redirect()->route('revenue_types.index')->with('success', 'تم إنشاء النوع بنجاح');
    }

    public function show(RevenueType $revenueType)
    {
        return view('revenue_types.show', compact('revenueType'));
    }

    public function edit(RevenueType $revenueType)
    {
        return view('revenue_types.edit', compact('revenueType'));
    }

    public function update(Request $request, RevenueType $revenueType)
    {
        $validated = $request->validate([
            'name' => 'required',
            'description' => 'nullable|string',
        ]);
        $revenueType->update($validated);
        return redirect()->route('revenue_types.index')->with('success', 'تم تحديث النوع بنجاح');
    }

    public function destroy(RevenueType $revenueType)
    {
        $revenueType->delete();
        return redirect()->route('revenue_types.index')->with('success', 'تم حذف النوع بنجاح');
    }
} 