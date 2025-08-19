@extends('prints.layout')

@section('content')
    @php
        $title = 'فاتورة شراء';
    @endphp

    <div class="document-info">
        <div>
            <div class="info-item">
                <span class="info-label">رقم الفاتورة:</span>
                <span>{{ $purchaseInvoice->invoice_no }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">التاريخ:</span>
                <span>{{ $purchaseInvoice->invoice_date }}</span>
            </div>
        </div>
        <div>
            <div class="info-item">
                <span class="info-label">المورد:</span>
                <span>{{ $purchaseInvoice->supplier ? $purchaseInvoice->supplier->name : '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">الحالة:</span>
                <span>{{ $purchaseInvoice->status }}</span>
            </div>
        </div>
    </div>

    <div style="text-align: center; margin: 40px 0;">
        <h2 style="color: #007bff; margin-bottom: 20px;">فاتورة شراء</h2>
        <div style="font-size: 24px; font-weight: bold; color: #dc3545; margin: 20px 0;">
            المبلغ الإجمالي: {{ number_format($purchaseInvoice->total_amount, 2) }} دينار ليبي
        </div>
    </div>

    <div style="margin: 40px 0;">
        <h4>تفاصيل الفاتورة</h4>
        <table class="table">
            <tr>
                <td><strong>رقم الفاتورة:</strong></td>
                <td>{{ $purchaseInvoice->invoice_no }}</td>
            </tr>
            <tr>
                <td><strong>التاريخ:</strong></td>
                <td>{{ $purchaseInvoice->invoice_date }}</td>
            </tr>
            <tr>
                <td><strong>المورد:</strong></td>
                <td>{{ $purchaseInvoice->supplier ? $purchaseInvoice->supplier->name : '-' }}</td>
            </tr>
            <tr>
                <td><strong>المبلغ الإجمالي:</strong></td>
                <td class="amount">{{ number_format($purchaseInvoice->total_amount, 2) }} دينار ليبي</td>
            </tr>
            <tr>
                <td><strong>الحالة:</strong></td>
                <td>{{ $purchaseInvoice->status }}</td>
            </tr>
            <tr>
                <td><strong>المرجع:</strong></td>
                <td>{{ $purchaseInvoice->reference_no ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>الملاحظات:</strong></td>
                <td>{{ $purchaseInvoice->notes ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>تم الإنشاء بواسطة:</strong></td>
                <td>{{ $purchaseInvoice->creator->name ?? 'النظام' }}</td>
            </tr>
        </table>
    </div>

    @if($purchaseInvoice->items && $purchaseInvoice->items->count() > 0)
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
                    @foreach($purchaseInvoice->items as $item)
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
                        <td class="text-right amount"><strong>{{ number_format($purchaseInvoice->total_amount, 2) }} دينار ليبي</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    <div style="margin: 40px 0; padding: 20px; border: 2px solid #007bff; border-radius: 8px; text-align: center;">
        <h4 style="color: #007bff; margin-bottom: 15px;">فاتورة شراء</h4>
        <p style="font-size: 16px; color: #666;">
            المبلغ الإجمالي: {{ number_format($purchaseInvoice->total_amount, 2) }} دينار ليبي
        </p>
        <p style="font-size: 14px; color: #666;">
            التاريخ: {{ $purchaseInvoice->invoice_date }}
        </p>
        <p style="font-size: 14px; color: #666;">
            المورد: {{ $purchaseInvoice->supplier ? $purchaseInvoice->supplier->name : '-' }}
        </p>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">توقيع المورد</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">توقيع المحاسب</div>
        </div>
    </div>
@endsection 