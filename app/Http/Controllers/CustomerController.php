<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::paginate(20);
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:customers',
            'name' => 'required',
        ]);
        Customer::create($validated + [
            'contact_person' => $request->input('contact_person'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'credit_limit' => $request->input('credit_limit', 0),
            'is_active' => $request->input('is_active', true),
        ]);
        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'code' => 'required|unique:customers,code,' . $customer->id,
            'name' => 'required',
        ]);
        $customer->update($validated + [
            'contact_person' => $request->input('contact_person'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'credit_limit' => $request->input('credit_limit', 0),
            'is_active' => $request->input('is_active', true),
        ]);
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['salesOrders', 'payments']);
        return view('customers.show', compact('customer'));
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
} 