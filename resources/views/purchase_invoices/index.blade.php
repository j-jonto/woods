@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">فواتير الشراء</h4>
    <a href="{{ route('purchase_invoices.create') }}" class="btn btn-success mb-3">إضافة فاتورة جديدة</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>الرقم</th>
                <th>المورد</th>
                <th>رقم الفاتورة</th>
                <th>التاريخ</th>
                <th>الإجمالي</th>
                <th>الحالة</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
            <tr>
                <td>{{ $invoice->id }}</td>
                <td>{{ $invoice->supplier ? $invoice->supplier->name : '-' }}</td>
                <td>{{ $invoice->invoice_no }}</td>
                <td>{{ $invoice->invoice_date }}</td>
                <td>{{ number_format($invoice->total_amount, 2) }}</td>
                <td>{{ $invoice->status }}</td>
                <td>
                    <a href="{{ route('purchase_invoices.show', $invoice->id) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('purchase_invoices.edit', $invoice->id) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <a href="{{ route('print.purchase_invoice', $invoice->id) }}" class="btn btn-success btn-sm" target="_blank">
                        <i class="fas fa-print"></i> طباعة
                    </a>
                    <form action="{{ route('purchase_invoices.destroy', $invoice->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $invoices->links() }}
</div>
@endsection 