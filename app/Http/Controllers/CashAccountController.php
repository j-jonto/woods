<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use Illuminate\Http\Request;

class CashAccountController extends Controller
{
    public function index()
    {
        $accounts = CashAccount::orderBy('name')->paginate(20);
        return view('cash_accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('cash_accounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'balance' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
        ]);
        CashAccount::create($validated);
        return redirect()->route('cash_accounts.index')->with('success', 'تم إنشاء الحساب بنجاح');
    }

    public function show(CashAccount $cashAccount)
    {
        return view('cash_accounts.show', compact('cashAccount'));
    }

    public function edit(CashAccount $cashAccount)
    {
        return view('cash_accounts.edit', compact('cashAccount'));
    }

    public function update(Request $request, CashAccount $cashAccount)
    {
        $validated = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'balance' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
        ]);
        $cashAccount->update($validated);
        return redirect()->route('cash_accounts.index')->with('success', 'تم تحديث الحساب بنجاح');
    }

    public function destroy(CashAccount $cashAccount)
    {
        $cashAccount->delete();
        return redirect()->route('cash_accounts.index')->with('success', 'تم حذف الحساب بنجاح');
    }
} 