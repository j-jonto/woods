<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use Illuminate\Http\Request;

class ExpenseTypeController extends Controller
{
    public function index()
    {
        $types = ExpenseType::orderBy('name')->paginate(20);
        return view('expense_types.index', compact('types'));
    }

    public function create()
    {
        return view('expense_types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'description' => 'nullable|string',
        ]);
        ExpenseType::create($validated);
        return redirect()->route('expense_types.index')->with('success', 'تم إنشاء النوع بنجاح');
    }

    public function show(ExpenseType $expenseType)
    {
        return view('expense_types.show', compact('expenseType'));
    }

    public function edit(ExpenseType $expenseType)
    {
        return view('expense_types.edit', compact('expenseType'));
    }

    public function update(Request $request, ExpenseType $expenseType)
    {
        $validated = $request->validate([
            'name' => 'required',
            'description' => 'nullable|string',
        ]);
        $expenseType->update($validated);
        return redirect()->route('expense_types.index')->with('success', 'تم تحديث النوع بنجاح');
    }

    public function destroy(ExpenseType $expenseType)
    {
        $expenseType->delete();
        return redirect()->route('expense_types.index')->with('success', 'تم حذف النوع بنجاح');
    }
} 