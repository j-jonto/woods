@extends('prints.layout')

@section('content')
    @php
        $title = 'تقرير الخزنة العامة';
    @endphp

    <div class="document-info">
        <div>
            <div class="info-item">
                <span class="info-label">اسم الخزنة:</span>
                <span>{{ $treasury->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">الرصيد الحالي:</span>
                <span class="amount">{{ number_format($treasury->current_balance, 2) }} دينار ليبي</span>
            </div>
        </div>
        <div>
            <div class="info-item">
                <span class="info-label">من تاريخ:</span>
                <span>{{ $from_date }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">إلى تاريخ:</span>
                <span>{{ $to_date }}</span>
            </div>
        </div>
    </div>

    <div style="margin-bottom: 30px;">
        <h4>ملخص الخزنة</h4>
        <table class="table">
            <tr>
                <td><strong>الرصيد الافتتاحي:</strong></td>
                <td class="text-right">{{ number_format($treasury->opening_balance, 2) }} دينار ليبي</td>
            </tr>
            <tr>
                <td><strong>إجمالي القبض:</strong></td>
                <td class="text-right amount">{{ number_format($treasury->total_receipts, 2) }} دينار ليبي</td>
            </tr>
            <tr>
                <td><strong>إجمالي الصرف:</strong></td>
                <td class="text-right amount">{{ number_format($treasury->total_payments, 2) }} دينار ليبي</td>
            </tr>
            <tr class="total-row">
                <td><strong>الرصيد الحالي:</strong></td>
                <td class="text-right amount">{{ number_format($treasury->current_balance, 2) }} دينار ليبي</td>
            </tr>
        </table>
    </div>

    <div>
        <h4>تفاصيل المعاملات</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>النوع</th>
                    <th>المبلغ</th>
                    <th>الرصيد بعد المعاملة</th>
                    <th>الوصف</th>
                    <th>المستخدم</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->transaction_date }}</td>
                        <td>
                            @if($transaction->type == 'receipt')
                                <span style="color: green; font-weight: bold;">قبض</span>
                            @elseif($transaction->type == 'payment')
                                <span style="color: red; font-weight: bold;">صرف</span>
                            @elseif($transaction->type == 'transfer')
                                <span style="color: blue; font-weight: bold;">تحويل</span>
                            @else
                                <span style="color: orange; font-weight: bold;">تسوية</span>
                            @endif
                        </td>
                        <td class="text-right amount">
                            @if($transaction->type == 'receipt')
                                +{{ number_format($transaction->amount, 2) }}
                            @else
                                -{{ number_format($transaction->amount, 2) }}
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($transaction->balance_after, 2) }} دينار ليبي</td>
                        <td>{{ $transaction->description }}</td>
                        <td>{{ $transaction->creator->name ?? 'النظام' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">لا توجد معاملات في هذه الفترة</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">توقيع المحاسب</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">توقيع المدير</div>
        </div>
    </div>
@endsection 