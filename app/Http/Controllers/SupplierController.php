<?php
namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::paginate(20);
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:suppliers',
            'name' => 'required',
        ]);
        Supplier::create($validated + [
            'contact_person' => $request->input('contact_person'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'is_active' => $request->input('is_active', true),
        ]);
        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'code' => 'required|unique:suppliers,code,' . $supplier->id,
            'name' => 'required',
        ]);
        $supplier->update($validated + [
            'contact_person' => $request->input('contact_person'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'is_active' => $request->input('is_active', true),
        ]);
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['purchaseOrders', 'payments']);
        return view('suppliers.show', compact('supplier'));
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
} 