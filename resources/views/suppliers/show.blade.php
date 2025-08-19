@extends('layouts.app')

@section('content')
<h3>كشف حساب المورد</h3>
<a href="{{ route('suppliers.add_payment', $supplier) }}" class="btn btn-success mb-3">إضافة إيصال صرف</a>
<a href="#" class="btn btn-outline-secondary mb-3" onclick="window.print();return false;">طباعة كشف الحساب</a>
@php
    $movements = [];
    foreach($supplier->purchaseOrders as $order) {
        $movements[] = [
            'type' => 'order',
            'date' => $order->order_date,
            'desc' => 'فاتورة مشتريات رقم ' . $order->order_no,
            'debit' => null,
            'credit' => $order->total_amount,
        ];
    }
    foreach($supplier->payments as $payment) {
        if($payment->type == 'disbursement') {
            $movements[] = [
                'type' => 'payment',
                'date' => $payment->payment_date,
                'desc' => 'إيصال صرف رقم ' . $payment->id,
                'debit' => $payment->amount,
                'credit' => null,
            ];
        }
    }
    usort($movements, fn($a, $b) => strcmp($a['date'], $b['date']));
    $balance = 0;
@endphp
<style>
@media print {
    body * { visibility: hidden; }
    .print-area, .print-area * { visibility: visible; }
    .print-area { position: absolute; left: 0; top: 0; width: 100%; }
    .btn, nav, header, footer { display: none !important; }
}
</style>
<div class="print-area">
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
                    $balance += ($move['credit'] ?? 0) - ($move['debit'] ?? 0);
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