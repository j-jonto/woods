<?php

namespace App\Http\Controllers;

use App\Models\PaymentVoucher;
use App\Models\CashAccount;
use App\Models\Treasury;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\DoubleEntryService;

class PaymentVoucherController extends Controller
{
    public function index()
    {
        $vouchers = PaymentVoucher::with('account')->orderByDesc('date')->paginate(20);
        return view('payment_vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        $accounts = CashAccount::all();
        return view('payment_vouchers.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:cash_accounts,id',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'destination' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $voucher = PaymentVoucher::create($validated + [
                'created_by' => $request->user()->id ?? null,
            ]);

            // ربط سند الصرف بالخزنة
            $this->deductPaymentFromTreasury($validated);

            // إنشاء قيد محاسبي للصرف النقدي (مدين مصروف/دائن نقدية)
            DoubleEntryService::createCashPaymentEntry(
                $validated['amount'],
                'سند صرف - ' . ($validated['destination'] ?? 'غير محدد'),
                'payment_voucher',
                $voucher->id ?? null
            );
        });

        return redirect()->route('payment_vouchers.index')->with('success', 'تم تسجيل سند الصرف بنجاح');
    }

    private function deductPaymentFromTreasury($data)
    {
        $treasury = Treasury::first();
        if (!$treasury) {
            throw new \Exception('لا توجد خزنة متاحة في النظام');
        }

        try {
            $treasury->addPayment(
                $data['amount'],
                'سند صرف - ' . ($data['destination'] ?? 'غير محدد'),
                'payment_voucher',
                null
            );
        } catch (\Exception $e) {
            \Log::error('خطأ في خصم سند الصرف من الخزنة', [
                'error' => $e->getMessage(),
                'amount' => $data['amount']
            ]);
            throw new \Exception('فشل في خصم سند الصرف من الخزنة: ' . $e->getMessage());
        }
    }

    public function show(PaymentVoucher $paymentVoucher)
    {
        $paymentVoucher->load('account');
        return view('payment_vouchers.show', compact('paymentVoucher'));
    }

    public function edit(PaymentVoucher $paymentVoucher)
    {
        $accounts = CashAccount::all();
        return view('payment_vouchers.edit', compact('paymentVoucher', 'accounts'));
    }

    public function update(Request $request, PaymentVoucher $paymentVoucher)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:cash_accounts,id',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'destination' => 'nullable|string',
        ]);
        $paymentVoucher->update($validated);
        return redirect()->route('payment_vouchers.index')->with('success', 'تم تحديث السند بنجاح');
    }

    public function destroy(PaymentVoucher $paymentVoucher)
    {
        $paymentVoucher->delete();
        return redirect()->route('payment_vouchers.index')->with('success', 'تم حذف السند بنجاح');
    }
} 