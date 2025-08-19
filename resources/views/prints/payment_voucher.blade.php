@extends('prints.layout')

@section('content')
    @php
        $title = 'سند صرف';
    @endphp

    <div class="document-info">
        <div>
            <div class="info-item">
                <span class="info-label">رقم السند:</span>
                <span>{{ $paymentVoucher->id }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">التاريخ:</span>
                <span>{{ $paymentVoucher->date }}</span>
            </div>
        </div>
        <div>
            <div class="info-item">
                <span class="info-label">الحساب:</span>
                <span>{{ $paymentVoucher->account ? $paymentVoucher->account->name : '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">الجهة المستفيدة:</span>
                <span>{{ $paymentVoucher->destination }}</span>
            </div>
        </div>
    </div>

    <div style="text-align: center; margin: 40px 0;">
        <h2 style="color: #007bff; margin-bottom: 20px;">سند صرف</h2>
        <div style="font-size: 24px; font-weight: bold; color: #dc3545; margin: 20px 0;">
            المبلغ: {{ number_format($paymentVoucher->amount, 2) }} دينار ليبي
        </div>
        <div style="font-size: 18px; color: #666; margin: 20px 0;">
            {{ $paymentVoucher->description ?? 'لا يوجد وصف' }}
        </div>
    </div>

    <div style="margin: 40px 0;">
        <h4>تفاصيل السند</h4>
        <table class="table">
            <tr>
                <td><strong>رقم السند:</strong></td>
                <td>{{ $paymentVoucher->id }}</td>
            </tr>
            <tr>
                <td><strong>التاريخ:</strong></td>
                <td>{{ $paymentVoucher->date }}</td>
            </tr>
            <tr>
                <td><strong>الحساب:</strong></td>
                <td>{{ $paymentVoucher->account ? $paymentVoucher->account->name : '-' }}</td>
            </tr>
            <tr>
                <td><strong>المبلغ:</strong></td>
                <td class="amount">{{ number_format($paymentVoucher->amount, 2) }} دينار ليبي</td>
            </tr>
            <tr>
                <td><strong>الجهة المستفيدة:</strong></td>
                <td>{{ $paymentVoucher->destination }}</td>
            </tr>
            <tr>
                <td><strong>المرجع:</strong></td>
                <td>{{ $paymentVoucher->reference_no ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>الملاحظات:</strong></td>
                <td>{{ $paymentVoucher->notes ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>تم الإنشاء بواسطة:</strong></td>
                <td>{{ $paymentVoucher->creator->name ?? 'النظام' }}</td>
            </tr>
        </table>
    </div>

    <div style="margin: 40px 0; padding: 20px; border: 2px solid #dc3545; border-radius: 8px; text-align: center;">
        <h4 style="color: #dc3545; margin-bottom: 15px;">تم صرف المبلغ المذكور أعلاه</h4>
        <p style="font-size: 16px; color: #666;">
            المبلغ: {{ number_format($paymentVoucher->amount, 2) }} دينار ليبي
        </p>
        <p style="font-size: 14px; color: #666;">
            التاريخ: {{ $paymentVoucher->date }}
        </p>
        <p style="font-size: 14px; color: #666;">
            الجهة المستفيدة: {{ $paymentVoucher->destination }}
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