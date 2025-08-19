<?php

namespace App\Http\Controllers;

use App\Models\ReceiptVoucher;
use App\Models\CashAccount;
use App\Models\Treasury;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\DoubleEntryService;

class ReceiptVoucherController extends Controller
{
    public function index()
    {
        $vouchers = ReceiptVoucher::with('account')->orderByDesc('date')->paginate(20);
        return view('receipt_vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        $accounts = CashAccount::all();
        return view('receipt_vouchers.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:cash_accounts,id',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'source' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $voucher = ReceiptVoucher::create($validated + [
                'created_by' => $request->user()->id ?? null,
            ]);

            // ربط سند القبض بالخزنة
            $this->addReceiptToTreasury($validated);

            // إنشاء قيد محاسبي للقبض النقدي (مدين نقدية/دائن إيراد)
            DoubleEntryService::createCashReceiptEntry(
                $validated['amount'],
                'سند قبض - ' . ($validated['source'] ?? 'غير محدد'),
                'receipt_voucher',
                $voucher->id ?? null
            );
        });

        return redirect()->route('receipt_vouchers.index')->with('success', 'تم تسجيل سند القبض بنجاح');
    }

    private function addReceiptToTreasury($data)
    {
        $treasury = Treasury::first();
        if (!$treasury) {
            throw new \Exception('لا توجد خزنة متاحة في النظام');
        }

        try {
            $treasury->addReceipt(
                $data['amount'],
                'سند قبض - ' . ($data['source'] ?? 'غير محدد'),
                'receipt_voucher',
                null
            );
        } catch (\Exception $e) {
            \Log::error('خطأ في إضافة سند القبض للخزنة', [
                'error' => $e->getMessage(),
                'amount' => $data['amount']
            ]);
            throw new \Exception('فشل في إضافة سند القبض للخزنة: ' . $e->getMessage());
        }
    }

    public function show(ReceiptVoucher $receiptVoucher)
    {
        $receiptVoucher->load('account');
        return view('receipt_vouchers.show', compact('receiptVoucher'));
    }

    public function edit(ReceiptVoucher $receiptVoucher)
    {
        $accounts = CashAccount::all();
        return view('receipt_vouchers.edit', compact('receiptVoucher', 'accounts'));
    }

    public function update(Request $request, ReceiptVoucher $receiptVoucher)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:cash_accounts,id',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'source' => 'nullable|string',
        ]);
        $receiptVoucher->update($validated);
        return redirect()->route('receipt_vouchers.index')->with('success', 'تم تحديث السند بنجاح');
    }

    public function destroy(ReceiptVoucher $receiptVoucher)
    {
        $receiptVoucher->delete();
        return redirect()->route('receipt_vouchers.index')->with('success', 'تم حذف السند بنجاح');
    }
} 