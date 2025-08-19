<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseType;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('expenseType')->orderByDesc('expense_date')->paginate(20);
        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        $types = ExpenseType::all();
        return view('expenses.create', compact('types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_type_id' => 'required|exists:expense_types,id',
            'amount' => 'required|numeric',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
            'reference_no' => 'nullable|string',
        ]);

        try {
            \DB::transaction(function () use ($validated, $request) {
                // إنشاء المصروف
                $expense = Expense::create($validated + [
                    'created_by' => $request->user()->id ?? null,
                ]);

                // خصم المصروف من الخزنة
                $this->deductExpenseFromTreasury($expense);
            });

            return redirect()->route('expenses.index')->with('success', 'تم تسجيل المصروف بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('expenses.create')->with('error', 'خطأ في تسجيل المصروف: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Expense $expense)
    {
        $expense->load('expenseType');
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $types = ExpenseType::all();
        return view('expenses.edit', compact('expense', 'types'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'expense_type_id' => 'required|exists:expense_types,id',
            'amount' => 'required|numeric',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
            'reference_no' => 'nullable|string',
        ]);
        $expense->update($validated);
        return redirect()->route('expenses.index')->with('success', 'تم تحديث المصروف بنجاح');
    }

    public function destroy(Expense $expense)
    {
        try {
            \DB::transaction(function () use ($expense) {
                // إعادة المصروف للخزنة
                $this->restoreExpenseToTreasury($expense);
                
                // حذف المصروف
                $expense->delete();
            });

            return redirect()->route('expenses.index')->with('success', 'تم حذف المصروف بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('expenses.index')->with('error', 'خطأ في حذف المصروف: ' . $e->getMessage());
        }
    }

    private function deductExpenseFromTreasury($expense)
    {
        $treasury = \App\Models\Treasury::first();
        if (!$treasury) {
            throw new \Exception('لا توجد خزنة متاحة في النظام');
        }

        try {
            $treasury->addPayment(
                $expense->amount,
                'مصروف - ' . ($expense->expenseType->name ?? 'غير محدد') . ' - ' . ($expense->reference_no ?? ''),
                'expense',
                $expense->id
            );
        } catch (\Exception $e) {
            \Log::error('خطأ في خصم المصروف من الخزنة', [
                'error' => $e->getMessage(),
                'expense_id' => $expense->id,
                'amount' => $expense->amount
            ]);
            throw new \Exception('فشل في خصم المصروف من الخزنة: ' . $e->getMessage());
        }
    }

    private function restoreExpenseToTreasury($expense)
    {
        $treasury = \App\Models\Treasury::first();
        if (!$treasury) {
            throw new \Exception('لا توجد خزنة متاحة في النظام');
        }

        try {
            $treasury->addReceipt(
                $expense->amount,
                'إلغاء مصروف - ' . ($expense->expenseType->name ?? 'غير محدد') . ' - ' . ($expense->reference_no ?? ''),
                'expense',
                $expense->id
            );
        } catch (\Exception $e) {
            \Log::error('خطأ في إعادة المصروف للخزنة', [
                'error' => $e->getMessage(),
                'expense_id' => $expense->id,
                'amount' => $expense->amount
            ]);
            throw new \Exception('فشل في إعادة المصروف للخزنة: ' . $e->getMessage());
        }
    }
} 