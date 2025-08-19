@extends('prints.layout')

@section('content')
    @php
        $title = 'تقرير المصروفات';
    @endphp

    <div class="document-info">
        <div>
            <div class="info-item">
                <span class="info-label">من تاريخ:</span>
                <span>{{ $from_date }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">إلى تاريخ:</span>
                <span>{{ $to_date }}</span>
            </div>
        </div>
        <div>
            <div class="info-item">
                <span class="info-label">عدد المصروفات:</span>
                <span>{{ $expenses->count() }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">إجمالي المصروفات:</span>
                <span class="amount">{{ number_format($expenses->sum('amount'), 2) }} دينار ليبي</span>
            </div>
        </div>
    </div>

    <div style="margin-bottom: 30px;">
        <h4>ملخص المصروفات</h4>
        <table class="table">
            <tr>
                <td><strong>إجمالي المصروفات:</strong></td>
                <td class="text-right amount">{{ number_format($expenses->sum('amount'), 2) }} دينار ليبي</td>
            </tr>
            <tr>
                <td><strong>عدد المصروفات:</strong></td>
                <td class="text-right">{{ $expenses->count() }}</td>
            </tr>
            <tr>
                <td><strong>متوسط المصروف:</strong></td>
                <td class="text-right">{{ $expenses->count() > 0 ? number_format($expenses->avg('amount'), 2) : '0.00' }} دينار ليبي</td>
            </tr>
        </table>
    </div>

    <div>
        <h4>تفاصيل المصروفات</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>نوع المصروف</th>
                    <th>المبلغ</th>
                    <th>الوصف</th>
                    <th>المرجع</th>
                    <th>المستخدم</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                    <tr>
                        <td>{{ $expense->date }}</td>
                        <td>{{ $expense->type ? $expense->type->name : '-' }}</td>
                        <td class="text-right amount">{{ number_format($expense->amount, 2) }} دينار ليبي</td>
                        <td>{{ $expense->description }}</td>
                        <td>{{ $expense->reference_no }}</td>
                        <td>{{ $expense->creator->name ?? 'النظام' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">لا توجد مصروفات في هذه الفترة</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2"><strong>المجموع</strong></td>
                    <td class="text-right amount"><strong>{{ number_format($expenses->sum('amount'), 2) }} دينار ليبي</strong></td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div style="margin-top: 30px;">
        <h4>توزيع المصروفات حسب النوع</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>نوع المصروف</th>
                    <th>عدد المصروفات</th>
                    <th>إجمالي المبلغ</th>
                    <th>النسبة المئوية</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalAmount = $expenses->sum('amount');
                    $expensesByType = $expenses->groupBy('type_id');
                @endphp
                @forelse($expensesByType as $typeId => $typeExpenses)
                    @php
                        $type = $typeExpenses->first()->type;
                        $typeTotal = $typeExpenses->sum('amount');
                        $percentage = $totalAmount > 0 ? ($typeTotal / $totalAmount) * 100 : 0;
                    @endphp
                    <tr>
                        <td>{{ $type ? $type->name : 'غير محدد' }}</td>
                        <td class="text-center">{{ $typeExpenses->count() }}</td>
                        <td class="text-right amount">{{ number_format($typeTotal, 2) }} دينار ليبي</td>
                        <td class="text-center">{{ number_format($percentage, 1) }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">لا توجد مصروفات</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">توقيع المحاسب</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">توقيع المدير</div>
        </div>
    </div>
@endsection 