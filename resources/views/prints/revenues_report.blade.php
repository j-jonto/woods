@extends('prints.layout')

@section('content')
    @php
        $title = 'تقرير الإيرادات';
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
                <span class="info-label">عدد الإيرادات:</span>
                <span>{{ $revenues->count() }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">إجمالي الإيرادات:</span>
                <span class="amount">{{ number_format($revenues->sum('amount'), 2) }} دينار ليبي</span>
            </div>
        </div>
    </div>

    <div style="margin-bottom: 30px;">
        <h4>ملخص الإيرادات</h4>
        <table class="table">
            <tr>
                <td><strong>إجمالي الإيرادات:</strong></td>
                <td class="text-right amount">{{ number_format($revenues->sum('amount'), 2) }} دينار ليبي</td>
            </tr>
            <tr>
                <td><strong>عدد الإيرادات:</strong></td>
                <td class="text-right">{{ $revenues->count() }}</td>
            </tr>
            <tr>
                <td><strong>متوسط الإيراد:</strong></td>
                <td class="text-right">{{ $revenues->count() > 0 ? number_format($revenues->avg('amount'), 2) : '0.00' }} دينار ليبي</td>
            </tr>
        </table>
    </div>

    <div>
        <h4>تفاصيل الإيرادات</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>نوع الإيراد</th>
                    <th>المبلغ</th>
                    <th>الوصف</th>
                    <th>المرجع</th>
                    <th>المستخدم</th>
                </tr>
            </thead>
            <tbody>
                @forelse($revenues as $revenue)
                    <tr>
                        <td>{{ $revenue->date }}</td>
                        <td>{{ $revenue->type ? $revenue->type->name : '-' }}</td>
                        <td class="text-right amount">{{ number_format($revenue->amount, 2) }} دينار ليبي</td>
                        <td>{{ $revenue->description }}</td>
                        <td>{{ $revenue->reference_no }}</td>
                        <td>{{ $revenue->creator->name ?? 'النظام' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">لا توجد إيرادات في هذه الفترة</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2"><strong>المجموع</strong></td>
                    <td class="text-right amount"><strong>{{ number_format($revenues->sum('amount'), 2) }} دينار ليبي</strong></td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div style="margin-top: 30px;">
        <h4>توزيع الإيرادات حسب النوع</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>نوع الإيراد</th>
                    <th>عدد الإيرادات</th>
                    <th>إجمالي المبلغ</th>
                    <th>النسبة المئوية</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalAmount = $revenues->sum('amount');
                    $revenuesByType = $revenues->groupBy('type_id');
                @endphp
                @forelse($revenuesByType as $typeId => $typeRevenues)
                    @php
                        $type = $typeRevenues->first()->type;
                        $typeTotal = $typeRevenues->sum('amount');
                        $percentage = $totalAmount > 0 ? ($typeTotal / $totalAmount) * 100 : 0;
                    @endphp
                    <tr>
                        <td>{{ $type ? $type->name : 'غير محدد' }}</td>
                        <td class="text-center">{{ $typeRevenues->count() }}</td>
                        <td class="text-right amount">{{ number_format($typeTotal, 2) }} دينار ليبي</td>
                        <td class="text-center">{{ number_format($percentage, 1) }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">لا توجد إيرادات</td>
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