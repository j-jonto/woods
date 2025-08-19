@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>تفاصيل المندوب: {{ $salesRepresentative->name }}</h3>
        <div>
            <a href="{{ route('representative_transactions.create', $salesRepresentative) }}" class="btn btn-success">إضافة حركة</a>
            <a href="{{ route('sales_representatives.edit', $salesRepresentative) }}" class="btn btn-warning">تعديل</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <h5>المعلومات الأساسية</h5>
            <p><strong>الاسم:</strong> {{ $salesRepresentative->name }}</p>
            <p><strong>الهاتف:</strong> {{ $salesRepresentative->phone ?? 'غير محدد' }}</p>
            <p><strong>البريد الإلكتروني:</strong> {{ $salesRepresentative->email ?? 'غير محدد' }}</p>
            <p><strong>العنوان:</strong> {{ $salesRepresentative->address ?? 'غير محدد' }}</p>
            <p><strong>نسبة العمولة:</strong> {{ $salesRepresentative->commission_rate }}%</p>
        </div>
        <div class="col-md-6">
            <h5>التقرير المالي</h5>
            <p><strong>إجمالي المبيعات:</strong> {{ number_format($salesRepresentative->total_sales, 2) }} دينار ليبي</p>
            <p><strong>العمولة المستحقة:</strong> {{ number_format($salesRepresentative->total_commission, 2) }} دينار ليبي</p>
            <p><strong>الرصيد الحالي:</strong> {{ number_format($salesRepresentative->balance, 2) }} دينار ليبي</p>
        </div>
    </div>

    <h5>كشف حساب المندوب</h5>
    @php
        $movements = [];
        foreach($salesRepresentative->transactions as $transaction) {
            $movements[] = [
                'type' => $transaction->type,
                'date' => $transaction->transaction_date,
                'desc' => $transaction->type == 'goods_received' ? 'بضاعة مستلمة - ' . $transaction->reference : 
                         ($transaction->type == 'payment' ? 'دفعة للشركة - ' . $transaction->reference : 'عمولة إضافية - ' . $transaction->reference),
                'debit' => $transaction->type == 'goods_received' ? $transaction->amount : null,
                'credit' => $transaction->type == 'payment' ? $transaction->amount : 
                           ($transaction->type == 'commission' ? $transaction->amount : null),
            ];
        }
        usort($movements, fn($a, $b) => strcmp($a['date'], $b['date']));
        $balance = 0;
    @endphp
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>البيان</th>
                <th>التاريخ</th>
                <th>مدين</th>
                <th>دائن</th>
                <th>الرصيد بعد الحركة</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $move)
                <tr>
                    <td>{{ $move['desc'] }}</td>
                    <td>{{ $move['date'] }}</td>
                    <td>{{ $move['debit'] ? number_format($move['debit'], 2) : '' }}</td>
                    <td>{{ $move['credit'] ? number_format($move['credit'], 2) : '' }}</td>
                    @php
                        $balance += ($move['debit'] ?? 0) - ($move['credit'] ?? 0);
                    @endphp
                    <td>{{ number_format($balance, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">الرصيد النهائي</th>
                <th>{{ number_format($balance, 2) }} دينار ليبي</th>
            </tr>
        </tfoot>
    </table>
</div>
@endsection 