<?php
namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function index()
    {
        $accounts = ChartOfAccount::with('children')->whereNull('parent_id')->get();
        return view('coa.index', compact('accounts'));
    }

    public function create()
    {
        $parents = ChartOfAccount::all();
        return view('coa.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:chart_of_accounts',
            'name' => 'required',
            'type' => 'required',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'level' => 'required|integer',
        ]);
        ChartOfAccount::create($validated);
        return redirect()->route('coa.index')->with('success', 'Account created successfully.');
    }

    public function edit(ChartOfAccount $coa)
    {
        $parents = ChartOfAccount::where('id', '!=', $coa->id)->get();
        return view('coa.edit', compact('coa', 'parents'));
    }

    public function update(Request $request, ChartOfAccount $coa)
    {
        $validated = $request->validate([
            'code' => 'required|unique:chart_of_accounts,code,' . $coa->id,
            'name' => 'required',
            'type' => 'required',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'level' => 'required|integer',
        ]);
        $coa->update($validated);
        return redirect()->route('coa.index')->with('success', 'Account updated successfully.');
    }

    public function destroy(ChartOfAccount $coa)
    {
        $coa->delete();
        return redirect()->route('coa.index')->with('success', 'Account deleted successfully.');
    }
} 