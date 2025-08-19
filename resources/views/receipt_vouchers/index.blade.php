@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">سندات القبض</h4>
    <a href="{{ route('receipt_vouchers.create') }}" class="btn btn-success mb-3">إضافة سند جديد</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>الرقم</th>
                <th>الحساب</th>
                <th>المبلغ</th>
                <th>التاريخ</th>
                <th>المصدر</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vouchers as $voucher)
            <tr>
                <td>{{ $voucher->id }}</td>
                <td>{{ $voucher->account ? $voucher->account->name : '-' }}</td>
                <td>{{ number_format($voucher->amount, 2) }}</td>
                <td>{{ $voucher->date }}</td>
                <td>{{ $voucher->source }}</td>
                <td>
                    <a href="{{ route('receipt_vouchers.show', $voucher->id) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('receipt_vouchers.edit', $voucher->id) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <a href="{{ route('print.receipt_voucher', $voucher->id) }}" class="btn btn-success btn-sm" target="_blank">
                        <i class="fas fa-print"></i> طباعة
                    </a>
                    <form action="{{ route('receipt_vouchers.destroy', $voucher->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $vouchers->links() }}
</div>
@endsection 