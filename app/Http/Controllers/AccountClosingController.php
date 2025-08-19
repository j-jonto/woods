<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\ChartOfAccount;
use App\Models\Treasury;
use App\Services\DoubleEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountClosingController extends Controller
{
    public function index()
    {
        $accounts = ChartOfAccount::with('type')->get();
        $treasury = Treasury::first();
        
        return view('account_closing.index', compact('accounts', 'treasury'));
    }

    public function closeMonth(Request $request)
    {
        $validated = $request->validate([
            'closing_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $closingDate = $validated['closing_date'];
                
                // إغلاق حسابات الإيرادات
                $this->closeRevenueAccounts($closingDate, $validated['description'] ?? 'إغلاق شهري');
                
                // إغلاق حسابات المصروفات
                $this->closeExpenseAccounts($closingDate, $validated['description'] ?? 'إغلاق شهري');
                
                // إغلاق حساب الأرباح والخسائر
                $this->closeProfitLossAccount($closingDate, $validated['description'] ?? 'إغلاق شهري');
            });

            return redirect()->route('account_closing.index')
                ->with('success', 'تم إغلاق الحسابات بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في إغلاق الحسابات: ' . $e->getMessage());
        }
    }

    public function closeYear(Request $request)
    {
        $validated = $request->validate([
            'closing_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $closingDate = $validated['closing_date'];
                
                // إغلاق شهري أولاً
                $this->closeMonth($request);
                
                // إغلاق حساب الأرباح المحتجزة
                $this->closeRetainedEarningsAccount($closingDate, $validated['description'] ?? 'إغلاق سنوي');
            });

            return redirect()->route('account_closing.index')
                ->with('success', 'تم إغلاق الحسابات السنوي بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في إغلاق الحسابات السنوي: ' . $e->getMessage());
        }
    }

    public function trialBalance(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now());
        
        $accounts = ChartOfAccount::with('type')->get();
        $trialBalance = [];

        foreach ($accounts as $account) {
            $balance = DoubleEntryService::getAccountBalance($account->id, $asOfDate);
            if ($balance != 0) {
                $trialBalance[] = [
                    'account' => $account,
                    'balance' => $balance,
                    'debit' => $balance > 0 ? $balance : 0,
                    'credit' => $balance < 0 ? abs($balance) : 0,
                ];
            }
        }

        $totalDebit = collect($trialBalance)->sum('debit');
        $totalCredit = collect($trialBalance)->sum('credit');

        return view('account_closing.trial_balance', compact('trialBalance', 'totalDebit', 'totalCredit', 'asOfDate'));
    }

    public function incomeStatement(Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth());
        $toDate = $request->get('to_date', now()->endOfMonth());

        // اجلب قيود GL ضمن الفترة للحسابات الإيرادية والمصروفية فقط (قيود مُرحلة)
        $items = \App\Models\JournalEntryItem::with('account')
            ->whereHas('journalEntry', function($q) use ($fromDate, $toDate) {
                $q->whereBetween('entry_date', [$fromDate, $toDate])
                  ->where('status', 'posted');
            })
            ->whereHas('account', function($q) {
                $q->whereIn('type', ['revenue', 'expense']);
            })
            ->get();

        $revenueByAccount = [];
        $expenseByAccount = [];

        foreach ($items as $it) {
            $code = $it->account->code;
            $type = $it->account->type; // revenue | expense
            $debit = (float)$it->debit;
            $credit = (float)$it->credit;
            if ($type === 'revenue') {
                $revenueByAccount[$code] = ($revenueByAccount[$code] ?? 0) + ($credit - $debit);
            } elseif ($type === 'expense') {
                $expenseByAccount[$code] = ($expenseByAccount[$code] ?? 0) + ($debit - $credit);
            }
        }

        $totalRevenue = array_sum($revenueByAccount);
        $totalExpenses = array_sum($expenseByAccount);
        $netIncome = $totalRevenue - $totalExpenses;

        // حضّر بنية شبيهة بالسابق للعرض
        $revenues = collect();
        foreach ($revenueByAccount as $code => $amount) {
            $acc = \App\Models\ChartOfAccount::where('code', $code)->first();
            if ($acc && abs($amount) > 0.0001) {
                $revenues->push(['account' => $acc, 'balance' => $amount]);
            }
        }
        $expenses = collect();
        foreach ($expenseByAccount as $code => $amount) {
            $acc = \App\Models\ChartOfAccount::where('code', $code)->first();
            if ($acc && abs($amount) > 0.0001) {
                $expenses->push(['account' => $acc, 'balance' => $amount]);
            }
        }

        return view('account_closing.income_statement', compact(
            'revenues', 'totalRevenue', 'expenses', 'totalExpenses', 'netIncome', 'fromDate', 'toDate'
        ));
    }

    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now());

        // الأصول
        $assets = $this->getAccountBalancesByType('asset', null, $asOfDate);
        $totalAssets = $assets->sum('balance');

        // الخصوم
        $liabilities = $this->getAccountBalancesByType('liability', null, $asOfDate);
        $totalLiabilities = $liabilities->sum('balance');

        // حقوق الملكية
        $equity = $this->getAccountBalancesByType('equity', null, $asOfDate);
        $totalEquity = $equity->sum('balance');

        return view('account_closing.balance_sheet', compact(
            'assets', 'totalAssets', 'liabilities', 'totalLiabilities', 'equity', 'totalEquity', 'asOfDate'
        ));
    }

    private function closeRevenueAccounts($closingDate, $description)
    {
        $revenueAccounts = ChartOfAccount::where('type', 'revenue')->get();
        
        foreach ($revenueAccounts as $account) {
            $balance = DoubleEntryService::getAccountBalance($account->id, $closingDate);
            
            if ($balance > 0) {
                $entry = JournalEntry::create([
                    'entry_date' => $closingDate,
                    'reference_no' => 'JE-CLOSE-REV-' . time(),
                    'description' => $description,
                    'entry_type' => 'adjustment',
                    'status' => 'draft',
                    'created_by' => auth()->id(),
                ]);

                // من ح/ الإيرادات (مدين) إلى ح/ الأرباح والخسائر (دائن)
                $entry->items()->create([
                    'account_id' => $account->id,
                    'debit' => $balance,
                    'credit' => 0,
                    'description' => 'إغلاق إيرادات',
                ]);

                $entry->items()->create([
                    'account_id' => $this->getProfitLossAccountId(),
                    'debit' => 0,
                    'credit' => $balance,
                    'description' => 'إغلاق إيرادات',
                ]);

                $entry->post();
            }
        }
    }

    private function closeExpenseAccounts($closingDate, $description)
    {
        $expenseAccounts = ChartOfAccount::where('type', 'expense')->get();
        
        foreach ($expenseAccounts as $account) {
            $balance = DoubleEntryService::getAccountBalance($account->id, $closingDate);
            
            if ($balance > 0) {
                $entry = JournalEntry::create([
                    'entry_date' => $closingDate,
                    'reference_no' => 'JE-CLOSE-EXP-' . time(),
                    'description' => $description,
                    'entry_type' => 'adjustment',
                    'status' => 'draft',
                    'created_by' => auth()->id(),
                ]);

                // من ح/ الأرباح والخسائر (مدين) إلى ح/ المصروفات (دائن)
                $entry->items()->create([
                    'account_id' => $this->getProfitLossAccountId(),
                    'debit' => $balance,
                    'credit' => 0,
                    'description' => 'إغلاق مصروفات',
                ]);

                $entry->items()->create([
                    'account_id' => $account->id,
                    'debit' => 0,
                    'credit' => $balance,
                    'description' => 'إغلاق مصروفات',
                ]);

                $entry->post();
            }
        }
    }

    private function closeProfitLossAccount($closingDate, $description)
    {
        $profitLossBalance = DoubleEntryService::getAccountBalance($this->getProfitLossAccountId(), $closingDate);
        
        if ($profitLossBalance != 0) {
            $entry = JournalEntry::create([
                'entry_date' => $closingDate,
                'reference_no' => 'JE-CLOSE-PL-' . time(),
                'description' => $description,
                'entry_type' => 'adjustment',
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            if ($profitLossBalance > 0) {
                // ربح: من ح/ الأرباح والخسائر (مدين) إلى ح/ الأرباح المحتجزة (دائن)
                $entry->items()->create([
                    'account_id' => $this->getProfitLossAccountId(),
                    'debit' => $profitLossBalance,
                    'credit' => 0,
                    'description' => 'إغلاق أرباح',
                ]);

                $entry->items()->create([
                    'account_id' => $this->getRetainedEarningsAccountId(),
                    'debit' => 0,
                    'credit' => $profitLossBalance,
                    'description' => 'إغلاق أرباح',
                ]);
            } else {
                // خسارة: من ح/ الأرباح المحتجزة (مدين) إلى ح/ الأرباح والخسائر (دائن)
                $entry->items()->create([
                    'account_id' => $this->getRetainedEarningsAccountId(),
                    'debit' => abs($profitLossBalance),
                    'credit' => 0,
                    'description' => 'إغلاق خسائر',
                ]);

                $entry->items()->create([
                    'account_id' => $this->getProfitLossAccountId(),
                    'debit' => 0,
                    'credit' => abs($profitLossBalance),
                    'description' => 'إغلاق خسائر',
                ]);
            }

            $entry->post();
        }
    }

    private function closeRetainedEarningsAccount($closingDate, $description)
    {
        $retainedEarningsBalance = DoubleEntryService::getAccountBalance($this->getRetainedEarningsAccountId(), $closingDate);
        
        if ($retainedEarningsBalance != 0) {
            $entry = JournalEntry::create([
                'entry_date' => $closingDate,
                'reference_no' => 'JE-CLOSE-RE-' . time(),
                'description' => $description,
                'entry_type' => 'adjustment',
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // من ح/ الأرباح المحتجزة (مدين) إلى ح/ رأس المال (دائن)
            $entry->items()->create([
                'account_id' => $this->getRetainedEarningsAccountId(),
                'debit' => $retainedEarningsBalance,
                'credit' => 0,
                'description' => 'إغلاق أرباح محتجزة',
            ]);

            $entry->items()->create([
                'account_id' => $this->getCapitalAccountId(),
                'debit' => 0,
                'credit' => $retainedEarningsBalance,
                'description' => 'إغلاق أرباح محتجزة',
            ]);

            $entry->post();
        }
    }

    private function getAccountBalancesByType($type, $fromDate = null, $toDate = null)
    {
        $accounts = ChartOfAccount::where('type', $type)->get();
        $balances = collect();

        foreach ($accounts as $account) {
            $balance = DoubleEntryService::getAccountBalance($account->id, $toDate);
            if ($balance != 0) {
                $balances->push([
                    'account' => $account,
                    'balance' => $balance,
                ]);
            }
        }

        return $balances;
    }

    private function getProfitLossAccountId()
    {
        return ChartOfAccount::where('code', '3001')->first()->id ?? 8;
    }

    private function getRetainedEarningsAccountId()
    {
        return ChartOfAccount::where('code', '3002')->first()->id ?? 9;
    }

    private function getCapitalAccountId()
    {
        return ChartOfAccount::where('code', '3000')->first()->id ?? 10;
    }
} 