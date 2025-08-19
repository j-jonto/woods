@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>أوامر المبيعات</h1>
        <a href="{{ route('sales_orders.create') }}" class="btn btn-primary">إنشاء أمر بيع جديد</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>رقم الطلب</th>
                <th>العميل</th>
                <th>تاريخ الطلب</th>
                <th>تاريخ التسليم</th>
                <th class="text-end">المبلغ الإجمالي</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td>{{ $order->order_no }}</td>
                    <td>{{ $order->customer->name }}</td>
                    <td>{{ \Illuminate\Support\Str::length($order->order_date) > 10 ? \Carbon\Carbon::parse($order->order_date)->format('Y-m-d') : $order->order_date }}</td>
                    <td>{{ $order->delivery_date ? (\Illuminate\Support\Str::length($order->delivery_date) > 10 ? \Carbon\Carbon::parse($order->delivery_date)->format('Y-m-d') : $order->delivery_date) : 'غير محدد' }}</td>
                    <td class="text-end">${{ number_format($order->total_amount, 2) }}</td>
                    <td><span class="badge bg-secondary">{{ ucfirst($order->status) }}</span></td>
                    <td>
                        <a href="{{ route('sales_orders.show', $order->id) }}" class="btn btn-sm btn-info">عرض</a>
                        @if ($order->status == 'draft' || $order->status == 'pending')
                            <a href="{{ route('sales_orders.edit', $order->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                            <form action="{{ route('sales_orders.destroy', $order->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف أمر البيع هذا؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $orders->links() }}
</div>
@endsection 