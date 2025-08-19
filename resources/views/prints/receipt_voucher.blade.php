@extends('prints.layout')

@section('content')
    @php
        $title = 'إيصال قبض';
    @endphp

    <div class="document-info">
        <div>
            <div class="info-item">
                <span class="info-label">رقم الإيصال:</span>
                <span>{{ $receiptVoucher->id }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">التاريخ:</span>
                <span>{{ $receiptVoucher->date }}</span>
            </div>
        </div>
        <div>
            <div class="info-item">
                <span class="info-label">الحساب:</span>
                <span>{{ $receiptVoucher->account ? $receiptVoucher->account->name : '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">المصدر:</span>
                <span>{{ $receiptVoucher->source }}</span>
            </div>
        </div>
    </div>

    <div style="text-align: center; margin: 40px 0;">
        <h2 style="color: #007bff; margin-bottom: 20px;">إيصال قبض</h2>
        <div style="font-size: 24px; font-weight: bold; color: #28a745; margin: 20px 0;">
            المبلغ: {{ number_format($receiptVoucher->amount, 2) }} دينار ليبي
        </div>
        <div style="font-size: 18px; color: #666; margin: 20px 0;">
            {{ $receiptVoucher->description ?? 'لا يوجد وصف' }}
        </div>
    </div>

    <div style="margin: 40px 0;">
        <h4>تفاصيل الإيصال</h4>
        <table class="table">
            <tr>
                <td><strong>رقم الإيصال:</strong></td>
                <td>{{ $receiptVoucher->id }}</td>
            </tr>
            <tr>
                <td><strong>التاريخ:</strong></td>
                <td>{{ $receiptVoucher->date }}</td>
            </tr>
            <tr>
                <td><strong>الحساب:</strong></td>
                <td>{{ $receiptVoucher->account ? $receiptVoucher->account->name : '-' }}</td>
            </tr>
            <tr>
                <td><strong>المبلغ:</strong></td>
                <td class="amount">{{ number_format($receiptVoucher->amount, 2) }} دينار ليبي</td>
            </tr>
            <tr>
                <td><strong>المصدر:</strong></td>
                <td>{{ $receiptVoucher->source }}</td>
            </tr>
            <tr>
                <td><strong>المرجع:</strong></td>
                <td>{{ $receiptVoucher->reference_no ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>الملاحظات:</strong></td>
                <td>{{ $receiptVoucher->notes ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>تم الإنشاء بواسطة:</strong></td>
                <td>{{ $receiptVoucher->creator->name ?? 'النظام' }}</td>
            </tr>
        </table>
    </div>

    <div style="margin: 40px 0; padding: 20px; border: 2px solid #28a745; border-radius: 8px; text-align: center;">
        <h4 style="color: #28a745; margin-bottom: 15px;">تم استلام المبلغ المذكور أعلاه</h4>
        <p style="font-size: 16px; color: #666;">
            المبلغ: {{ number_format($receiptVoucher->amount, 2) }} دينار ليبي
        </p>
        <p style="font-size: 14px; color: #666;">
            التاريخ: {{ $receiptVoucher->date }}
        </p>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">توقيع المستلم</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">توقيع المحاسب</div>
        </div>
    </div>
@endsection 