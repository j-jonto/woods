@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>معاملات المخزون</h1>
        <a href="{{ route('inventory_transactions.create') }}" class="btn btn-primary">تسجيل معاملة جديدة</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>رقم المرجع</th>
                <th>النوع</th>
                <th>الصنف</th>
                <th>المخزن</th>
                <th class="text-end">الكمية</th>
                <th class="text-end">تكلفة الوحدة</th>
                <th>رقم الدفعة</th>
                <th>أنشئ بواسطة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $transaction)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d') }}</td>
                    <td>{{ $transaction->reference_no }}</td>
                    <td><span class="badge bg-info">{{ ucfirst($transaction->type) }}</span></td>
                    <td>{{ $transaction->item->name }} ({{ $transaction->item->code }})</td>
                    <td>{{ $transaction->warehouse->name }}</td>
                    <td class="text-end">{{ number_format($transaction->quantity, 2) }}</td>
                    <td class="text-end">${{ number_format($transaction->unit_cost, 2) }}</td>
                    <td>{{ $transaction->batch_no ?? 'غير متوفر' }}</td>
                    <td>{{ $transaction->creator->name ?? 'النظام' }}</td>
                    <td>
                        <a href="{{ route('inventory_transactions.show', $transaction->id) }}" class="btn btn-sm btn-info">عرض</a>
                        <form action="{{ route('inventory_transactions.destroy', $transaction->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه المعاملة؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $transactions->links() }}
</div>
@endsection 