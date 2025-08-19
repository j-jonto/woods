<?php

namespace App\Http\Controllers;

use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TreasuryController extends Controller
{
    public function index()
    {
        $treasury = Treasury::first();
        $transactions = null;
        
        if ($treasury) {
            $transactions = TreasuryTransaction::where('treasury_id', $treasury->id)
                ->with('creator')
                ->orderByDesc('transaction_date')
                ->paginate(20);
        }
        
        return view('treasury.index', compact('treasury', 'transactions'));
    }

    public function show(Treasury $treasury)
    {
        $treasury->load(['transactions' => function($query) {
            $query->orderByDesc('transaction_date')->take(50);
        }]);
        
        $summary = $treasury->getSummary();
        
        return view('treasury.show', compact('treasury', 'summary'));
    }

    public function report(Request $request)
    {
        $treasury = Treasury::first();
        if (!$treasury) {
            return redirect()->back()->with('error', 'لا توجد خزنة متاحة');
        }

        $from = $request->get('from', now()->startOfMonth());
        $to = $request->get('to', now()->endOfMonth());

        $transactions = TreasuryTransaction::where('treasury_id', $treasury->id)
            ->whereBetween('transaction_date', [$from, $to])
            ->orderByDesc('transaction_date')
            ->paginate(50);

        $summary = [
            'opening_balance' => $treasury->opening_balance,
            'total_receipts' => $transactions->where('type', 'receipt')->sum('amount'),
            'total_payments' => $transactions->where('type', 'payment')->sum('amount'),
            'current_balance' => $treasury->current_balance,
        ];

        return view('treasury.report', compact('treasury', 'transactions', 'summary', 'from', 'to'));
    }

    public function balance()
    {
        $treasury = Treasury::first();
        if (!$treasury) {
            return response()->json(['error' => 'لا توجد خزنة متاحة'], 404);
        }

        return response()->json([
            'balance' => $treasury->current_balance,
            'formatted_balance' => number_format($treasury->current_balance, 2) . ' دينار ليبي',
            'last_updated' => $treasury->updated_at->format('Y-m-d H:i:s')
        ]);
    }

    public function transactions(Request $request)
    {
        $treasury = Treasury::first();
        if (!$treasury) {
            return response()->json(['error' => 'لا توجد خزنة متاحة'], 404);
        }

        $transactions = TreasuryTransaction::where('treasury_id', $treasury->id)
            ->orderByDesc('transaction_date')
            ->take(20)
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'date' => $transaction->transaction_date->format('Y-m-d'),
                    'balance_after' => $transaction->balance_after,
                ];
            });

        return response()->json($transactions);
    }

    public function edit(Treasury $treasury)
    {
        return view('treasury.edit', compact('treasury'));
    }

    public function update(Request $request, Treasury $treasury)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'opening_balance' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $treasury->update($request->all());

        return redirect()->route('treasury.index')->with('success', 'تم تحديث الخزنة بنجاح');
    }

    public function addReceipt(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'transaction_date' => 'required|date',
        ]);

        $treasury = Treasury::first();
        if (!$treasury) {
            return redirect()->back()->with('error', 'لا توجد خزنة متاحة');
        }

        try {
            $treasury->addReceipt(
                $request->amount,
                $request->description,
                'manual',
                null
            );

            return redirect()->route('treasury.index')->with('success', 'تم إضافة القبض بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function addPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'transaction_date' => 'required|date',
        ]);

        $treasury = Treasury::first();
        if (!$treasury) {
            return redirect()->back()->with('error', 'لا توجد خزنة متاحة');
        }

        try {
            $treasury->addPayment(
                $request->amount,
                $request->description,
                'manual',
                null
            );

            return redirect()->route('treasury.index')->with('success', 'تم إضافة الصرف بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
}
