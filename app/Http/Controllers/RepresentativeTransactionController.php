<?php

namespace App\Http\Controllers;

use App\Models\RepresentativeTransaction;
use App\Models\SalesRepresentative;
use Illuminate\Http\Request;

class RepresentativeTransactionController extends Controller
{
    public function createForRepresentative(SalesRepresentative $salesRepresentative)
    {
        return view('representative_transactions.create', compact('salesRepresentative'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'representative_id' => 'required|exists:sales_representatives,id',
            'type' => 'required|in:goods_received,payment,commission',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = $request->user()->id;
        RepresentativeTransaction::create($validated);

        $typeMessages = [
            'goods_received' => 'تم تسجيل بضاعة مستلمة',
            'payment' => 'تم تسجيل دفعة للشركة',
            'commission' => 'تم تسجيل عمولة إضافية'
        ];

        return redirect()->route('sales_representatives.show', $validated['representative_id'])
            ->with('success', $typeMessages[$validated['type']] ?? 'تم إضافة الحركة بنجاح');
    }
} 