@extends('prints.layout')

@section('content')
    @php
        $title = 'فاتورة مبيعات';
    @endphp

    <div class="document-info">
        <div>
            <div class="info-item">
                <span class="info-label">رقم الطلب:</span>
                <span>{{ $salesOrder->order_no }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">التاريخ:</span>
                <span>{{ $salesOrder->order_date }}</span>
            </div>
        </div>
        <div>
            <div class="info-item">
                <span class="info-label">العميل:</span>
                <span>{{ $salesOrder->customer ? $salesOrder->customer->name : '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">الحالة:</span>
                <span>{{ $salesOrder->status }}</span>
            </div>
        </div>
    </div>

    <div style="text-align: center; margin: 40px 0;">
        <h2 style="color: #007bff; margin-bottom: 20px;">فاتورة مبيعات</h2>
        <div style="font-size: 24px; font-weight: bold; color: #28a745; margin: 20px 0;">
            المبلغ الإجمالي: {{ number_format($salesOrder->total_amount, 2) }} دينار ليبي
        </div>
    </div>

    <div style="margin: 40px 0;">
        <h4>تفاصيل الطلب</h4>
        <table class="table">
            <tr>
                <td><strong>رقم الطلب:</strong></td>
                <td>{{ $salesOrder->order_no }}</td>
            </tr>
            <tr>
                <td><strong>التاريخ:</strong></td>
                <td>{{ $salesOrder->order_date }}</td>
            </tr>
            <tr>
                <td><strong>العميل:</strong></td>
                <td>{{ $salesOrder->customer ? $salesOrder->customer->name : '-' }}</td>
            </tr>
            <tr>
                <td><strong>المبلغ الإجمالي:</strong></td>
                <td class="amount">{{ number_format($salesOrder->total_amount, 2) }} دينار ليبي</td>
            </tr>
            <tr>
                <td><strong>الحالة:</strong></td>
                <td>{{ $salesOrder->status }}</td>
            </tr>
            <tr>
                <td><strong>المرجع:</strong></td>
                <td>{{ $salesOrder->reference_no ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>الملاحظات:</strong></td>
                <td>{{ $salesOrder->notes ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>تم الإنشاء بواسطة:</strong></td>
                <td>{{ $salesOrder->creator->name ?? 'النظام' }}</td>
            </tr>
        </table>
    </div>

    @if($salesOrder->items && $salesOrder->items->count() > 0)
        <div style="margin: 40px 0;">
            <h4>تفاصيل الأصناف</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>الصنف</th>
                        <th>الكمية</th>
                        <th>سعر الوحدة</th>
                        <th>المجموع</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesOrder->items as $item)
                        <tr>
                            <td>{{ $item->item ? $item->item->name : '-' }}</td>
                            <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                            <td class="text-right">{{ number_format($item->unit_price, 2) }} دينار ليبي</td>
                            <td class="text-right amount">{{ number_format($item->amount, 2) }} دينار ليبي</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3"><strong>المجموع</strong></td>
                        <td class="text-right amount"><strong>{{ number_format($salesOrder->total_amount, 2) }} دينار ليبي</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    <div style="margin: 40px 0; padding: 20px; border: 2px solid #28a745; border-radius: 8px; text-align: center;">
        <h4 style="color: #28a745; margin-bottom: 15px;">فاتورة مبيعات</h4>
        <p style="font-size: 16px; color: #666;">
            المبلغ الإجمالي: {{ number_format($salesOrder->total_amount, 2) }} دينار ليبي
        </p>
        <p style="font-size: 14px; color: #666;">
            التاريخ: {{ $salesOrder->order_date }}
        </p>
        <p style="font-size: 14px; color: #666;">
            العميل: {{ $salesOrder->customer ? $salesOrder->customer->name : '-' }}
        </p>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">توقيع العميل</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">توقيع مندوب المبيعات</div>
        </div>
    </div>
@endsection 