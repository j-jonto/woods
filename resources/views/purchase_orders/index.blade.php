@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>أوامر الشراء</h1>
        <a href="{{ route('purchase_orders.create') }}" class="btn btn-primary">إنشاء أمر شراء جديد</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>رقم الطلب</th>
                <th>المورد</th>
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
                    <td>{{ $order->supplier->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($order->order_date)->format('Y-m-d') }}</td>
                    <td>{{ $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('Y-m-d') : 'غير محدد' }}</td>
                    <td class="text-end">${{ number_format($order->total_amount, 2) }}</td>
                    <td>
                        @if($order->status == 'draft')
                            <span class="badge bg-secondary">مسودة</span>
                        @elseif($order->status == 'ordered')
                            <span class="badge bg-warning">مطلوب</span>
                        @elseif($order->status == 'received')
                            <span class="badge bg-success">مستلم</span>
                        @else
                            <span class="badge bg-danger">ملغي</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <a href="{{ route('purchase_orders.show', $order->id) }}" class="btn btn-sm btn-info" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            @if ($order->status == 'draft' || $order->status == 'ordered')
                                <a href="{{ route('purchase_orders.edit', $order->id) }}" class="btn btn-sm btn-warning" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            
                            @if ($order->status == 'draft')
                                <form action="{{ route('purchase_orders.confirm', $order->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-success" title="تأكيد الطلب"
                                            onclick="return confirm('هل أنت متأكد من تأكيد أمر الشراء: {{ $order->order_no }}؟')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                            @endif
                            
                            @if ($order->status == 'ordered')
                                <form action="{{ route('purchase_orders.receive', $order->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-primary" title="استلام البضاعة"
                                            onclick="return confirm('هل أنت متأكد من استلام البضاعة: {{ $order->order_no }}؟')">
                                        <i class="fas fa-truck"></i>
                                    </button>
                                </form>
                            @endif
                            
                            @if ($order->status != 'received' && $order->status != 'cancelled')
                                <form action="{{ route('purchase_orders.destroy', $order->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف"
                                            onclick="return confirm('هل أنت متأكد من حذف أمر الشراء: {{ $order->order_no }}؟')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $orders->links() }}
</div>
@endsection 