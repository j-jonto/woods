@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">مدفوعات الموردين</h4>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('supplier_payments.create') }}" class="btn btn-success">إضافة دفعة جديدة</a>
        <a href="{{ route('print.supplier_payments_report') }}" class="btn btn-info" target="_blank">
            <i class="fas fa-print"></i> طباعة تقرير المدفوعات
        </a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>الرقم</th>
                <th>المورد</th>
                <th>المبلغ</th>
                <th>التاريخ</th>
                <th>طريقة الدفع</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->id }}</td>
                <td>{{ $payment->supplier ? $payment->supplier->name : '-' }}</td>
                                                                <td>{{ number_format($payment->amount, 2) }} دينار ليبي</td>
                <td>{{ $payment->payment_date }}</td>
                <td>{{ $payment->method }}</td>
                <td>
                    <a href="{{ route('supplier_payments.show', $payment->id) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('supplier_payments.edit', $payment->id) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <form action="{{ route('supplier_payments.destroy', $payment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $payments->links() }}
</div>
@endsection 