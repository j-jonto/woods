<?php

namespace App\Http\Controllers;

use App\Models\SalesRepresentative;
use Illuminate\Http\Request;

class SalesRepresentativeController extends Controller
{
    public function index()
    {
        $representatives = SalesRepresentative::paginate(20);
        return view('sales_representatives.index', compact('representatives'));
    }

    public function create()
    {
        return view('sales_representatives.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        SalesRepresentative::create($validated);
        return redirect()->route('sales_representatives.index')->with('success', 'تم إضافة المندوب بنجاح');
    }

    public function show(SalesRepresentative $salesRepresentative)
    {
        $salesRepresentative->load(['salesOrders', 'transactions']);
        return view('sales_representatives.show', compact('salesRepresentative'));
    }

    public function edit(SalesRepresentative $salesRepresentative)
    {
        return view('sales_representatives.edit', compact('salesRepresentative'));
    }

    public function update(Request $request, SalesRepresentative $salesRepresentative)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $salesRepresentative->update($validated);
        return redirect()->route('sales_representatives.index')->with('success', 'تم تحديث بيانات المندوب بنجاح');
    }

    public function destroy(SalesRepresentative $salesRepresentative)
    {
        $salesRepresentative->delete();
        return redirect()->route('sales_representatives.index')->with('success', 'تم حذف المندوب بنجاح');
    }
} 