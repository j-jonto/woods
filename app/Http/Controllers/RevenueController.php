<?php

namespace App\Http\Controllers;

use App\Models\Revenue;
use App\Models\RevenueType;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    public function index()
    {
        $revenues = Revenue::with('revenueType')->orderByDesc('revenue_date')->paginate(20);
        return view('revenues.index', compact('revenues'));
    }

    public function create()
    {
        $types = RevenueType::all();
        return view('revenues.create', compact('types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'revenue_type_id' => 'required|exists:revenue_types,id',
            'amount' => 'required|numeric',
            'revenue_date' => 'required|date',
            'description' => 'nullable|string',
            'reference_no' => 'nullable|string',
        ]);

        try {
            \DB::transaction(function () use ($validated, $request) {
                // إنشاء الإيراد
                $revenue = Revenue::create($validated + [
                    'created_by' => $request->user()->id ?? null,
                ]);

                // إضافة الإيراد للخزنة
                $this->addRevenueToTreasury($revenue);
            });

            return redirect()->route('revenues.index')->with('success', 'تم تسجيل الإيراد بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('revenues.create')->with('error', 'خطأ في تسجيل الإيراد: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Revenue $revenue)
    {
        $revenue->load('revenueType');
        return view('revenues.show', compact('revenue'));
    }

    public function edit(Revenue $revenue)
    {
        $types = RevenueType::all();
        return view('revenues.edit', compact('revenue', 'types'));
    }

    public function update(Request $request, Revenue $revenue)
    {
        $validated = $request->validate([
            'revenue_type_id' => 'required|exists:revenue_types,id',
            'amount' => 'required|numeric',
            'revenue_date' => 'required|date',
            'description' => 'nullable|string',
            'reference_no' => 'nullable|string',
        ]);
        $revenue->update($validated);
        return redirect()->route('revenues.index')->with('success', 'تم تحديث الإيراد بنجاح');
    }

    public function destroy(Revenue $revenue)
    {
        try {
            \DB::transaction(function () use ($revenue) {
                // إزالة الإيراد من الخزنة
                $this->removeRevenueFromTreasury($revenue);
                
                // حذف الإيراد
                $revenue->delete();
            });

            return redirect()->route('revenues.index')->with('success', 'تم حذف الإيراد بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('revenues.index')->with('error', 'خطأ في حذف الإيراد: ' . $e->getMessage());
        }
    }

    private function addRevenueToTreasury($revenue)
    {
        $treasury = \App\Models\Treasury::first();
        if (!$treasury) {
            throw new \Exception('لا توجد خزنة متاحة في النظام');
        }

        try {
            $treasury->addReceipt(
                $revenue->amount,
                'إيراد - ' . ($revenue->revenueType->name ?? 'غير محدد') . ' - ' . ($revenue->reference_no ?? ''),
                'revenue',
                $revenue->id
            );
        } catch (\Exception $e) {
            \Log::error('خطأ في إضافة الإيراد للخزنة', [
                'error' => $e->getMessage(),
                'revenue_id' => $revenue->id,
                'amount' => $revenue->amount
            ]);
            throw new \Exception('فشل في إضافة الإيراد للخزنة: ' . $e->getMessage());
        }
    }

    private function removeRevenueFromTreasury($revenue)
    {
        $treasury = \App\Models\Treasury::first();
        if (!$treasury) {
            throw new \Exception('لا توجد خزنة متاحة في النظام');
        }

        try {
            $treasury->addPayment(
                $revenue->amount,
                'إلغاء إيراد - ' . ($revenue->revenueType->name ?? 'غير محدد') . ' - ' . ($revenue->reference_no ?? ''),
                'revenue',
                $revenue->id
            );
        } catch (\Exception $e) {
            \Log::error('خطأ في إزالة الإيراد من الخزنة', [
                'error' => $e->getMessage(),
                'revenue_id' => $revenue->id,
                'amount' => $revenue->amount
            ]);
            throw new \Exception('فشل في إزالة الإيراد من الخزنة: ' . $e->getMessage());
        }
    }
} 