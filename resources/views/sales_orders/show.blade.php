@extends('layouts.app')

@section('content')
<div class="container">
    <h1>تفاصيل أمر البيع: {{ $salesOrder->order_no }}</h1>

    <div class="card mb-4">
        <div class="card-header">
            معلومات الطلب
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>رقم الطلب:</strong> {{ $salesOrder->order_no }}</p>
                    <p><strong>العميل:</strong> {{ $salesOrder->customer->name }}</p>
                    <p><strong>تاريخ الطلب:</strong> {{ $salesOrder->order_date ? $salesOrder->order_date->format('Y-m-d') : 'غير محدد' }}</p>
                    <p><strong>تاريخ التسليم:</strong> {{ $salesOrder->delivery_date ? $salesOrder->delivery_date->format('Y-m-d') : 'غير محدد' }}</p>
                    <p><strong>طريقة الدفع:</strong> {{ $salesOrder->payment_type == 'cash' ? 'نقدًا' : 'آجل' }}</p>
                    <p><strong>مندوب المبيعات:</strong> {{ $salesOrder->representative?->name ?? 'غير محدد' }}</p>
                    @if($salesOrder->representative)
                        <p><strong>نسبة العمولة:</strong> {{ $salesOrder->representative->commission_rate }}%</p>
                        <p><strong>العمولة المستحقة:</strong> {{ number_format($salesOrder->total_amount * ($salesOrder->representative->commission_rate / 100), 2) }} دينار ليبي</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <p><strong>المبلغ الإجمالي:</strong> {{ number_format($salesOrder->total_amount, 2) }} دينار ليبي</p>
                    <p><strong>الحالة:</strong> <span class="badge bg-secondary">{{ ucfirst($salesOrder->status) }}</span></p>
                    <p><strong>ملاحظات:</strong> {{ $salesOrder->notes ?? 'غير محدد' }}</p>
                    <p><strong>تم الإنشاء بواسطة:</strong> {{ $salesOrder->creator->name ?? 'النظام' }}</p>
                    <p><strong>آخر تحديث:</strong> {{ $salesOrder->updated_at ? $salesOrder->updated_at->format('Y-m-d H:i:s') : 'غير محدد' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            أصناف الطلب
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th class="text-end">الكمية</th>
                        <th class="text-end">سعر الوحدة</th>
                        <th class="text-end">المجموع</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salesOrder->items as $item)
                        <tr>
                            <td>{{ $item->item->name }} ({{ $item->item->code }})</td>
                            <td class="text-end">{{ number_format($item->quantity, 2) }} {{ $item->item->unit_of_measure }}</td>
                            <td class="text-end">{{ number_format($item->unit_price, 2) }} دينار ليبي</td>
                            <td class="text-end">{{ number_format($item->quantity * $item->unit_price, 2) }} دينار ليبي</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">المبلغ الإجمالي:</th>
                        <th class="text-end">{{ number_format($salesOrder->total_amount, 2) }} دينار ليبي</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('sales_orders.index') }}" class="btn btn-secondary">العودة للقائمة</a>
        @if ($salesOrder->status == 'draft' || $salesOrder->status == 'pending')
            <a href="{{ route('sales_orders.edit', $salesOrder->id) }}" class="btn btn-warning">تعديل الطلب</a>
            @if ($salesOrder->status == 'draft')
                <form action="{{ route('sales_orders.update_status', $salesOrder->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من تأكيد أمر البيع هذا؟');">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="pending">
                    <button type="submit" class="btn btn-success">تأكيد الطلب</button>
                </form>
            @endif
        @endif
        @if ($salesOrder->status == 'pending' || $salesOrder->status == 'confirmed')
            <form action="{{ route('sales_orders.update_status', $salesOrder->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من شحن أمر البيع هذا؟');">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="shipped">
                <button type="submit" class="btn btn-primary">شحن الطلب</button>
            </form>
        @endif
        @if ($salesOrder->status == 'shipped')
            <form action="{{ route('sales_orders.update_status', $salesOrder->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من تحديد أمر البيع هذا كمُسلم؟');">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="delivered">
                <button type="submit" class="btn btn-info">تحديد كمُسلم</button>
            </form>
        @endif
        @if ($salesOrder->status != 'cancelled')
            <form action="{{ route('sales_orders.update_status', $salesOrder->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من إلغاء أمر البيع هذا؟');">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" class="btn btn-danger">إلغاء الطلب</button>
            </form>
        @endif
    </div>
</div>
@endsection 