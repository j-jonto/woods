@extends('prints.layout')

@section('content')
    @php
        $title = 'تقرير مدفوعات الموردين';
    @endphp

    <div class="document-info">
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
        <div>
            <div class="info-item">
                <span class="info-label">عدد المدفوعات:</span>
                <span>{{ $payments->count() }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">إجمالي المدفوعات:</span>
                <span class="amount">{{ number_format($payments->sum('amount'), 2) }} دينار ليبي</span>
            </div>
        </div>
    </div>

    <div style="margin-bottom: 30px;">
        <h4>ملخص مدفوعات الموردين</h4>
        <table class="table">
            <tr>
                <td><strong>إجمالي المدفوعات:</strong></td>
                <td class="text-right amount">{{ number_format($payments->sum('amount'), 2) }} دينار ليبي</td>
            </tr>
            <tr>
                <td><strong>عدد المدفوعات:</strong></td>
                <td class="text-right">{{ $payments->count() }}</td>
            </tr>
            <tr>
                <td><strong>متوسط المدفوعات:</strong></td>
                <td class="text-right">{{ $payments->count() > 0 ? number_format($payments->avg('amount'), 2) : '0.00' }} دينار ليبي</td>
            </tr>
            <tr>
                <td><strong>عدد الموردين:</strong></td>
                <td class="text-right">{{ $payments->unique('supplier_id')->count() }}</td>
            </tr>
        </table>
    </div>

    <div>
        <h4>تفاصيل مدفوعات الموردين</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>المورد</th>
                    <th>المبلغ</th>
                    <th>طريقة الدفع</th>
                    <th>المرجع</th>
                    <th>الملاحظات</th>
                    <th>المستخدم</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_date }}</td>
                        <td>{{ $payment->supplier ? $payment->supplier->name : '-' }}</td>
                        <td class="text-right amount">{{ number_format($payment->amount, 2) }} دينار ليبي</td>
                        <td>{{ $payment->method }}</td>
                        <td>{{ $payment->reference_no }}</td>
                        <td>{{ $payment->notes ?? '-' }}</td>
                        <td>{{ $payment->creator->name ?? 'النظام' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">لا توجد مدفوعات في هذه الفترة</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2"><strong>المجموع</strong></td>
                    <td class="text-right amount"><strong>{{ number_format($payments->sum('amount'), 2) }} دينار ليبي</strong></td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div style="margin-top: 30px;">
        <h4>توزيع المدفوعات حسب الموردين</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>المورد</th>
                    <th>عدد المدفوعات</th>
                    <th>إجمالي المدفوعات</th>
                    <th>متوسط المدفوعات</th>
                    <th>النسبة المئوية</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalPayments = $payments->sum('amount');
                    $paymentsBySupplier = $payments->groupBy('supplier_id');
                @endphp
                @forelse($paymentsBySupplier as $supplierId => $supplierPayments)
                    @php
                        $supplier = $supplierPayments->first()->supplier;
                        $supplierTotal = $supplierPayments->sum('amount');
                        $supplierAvg = $supplierPayments->avg('amount');
                        $percentage = $totalPayments > 0 ? ($supplierTotal / $totalPayments) * 100 : 0;
                    @endphp
                    <tr>
                        <td>{{ $supplier ? $supplier->name : 'غير محدد' }}</td>
                        <td class="text-center">{{ $supplierPayments->count() }}</td>
                        <td class="text-right amount">{{ number_format($supplierTotal, 2) }} دينار ليبي</td>
                        <td class="text-right">{{ number_format($supplierAvg, 2) }} دينار ليبي</td>
                        <td class="text-center">{{ number_format($percentage, 1) }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">لا توجد مدفوعات</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 30px;">
        <h4>توزيع المدفوعات حسب طريقة الدفع</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>طريقة الدفع</th>
                    <th>عدد المدفوعات</th>
                    <th>إجمالي المدفوعات</th>
                    <th>النسبة المئوية</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $paymentsByMethod = $payments->groupBy('method');
                @endphp
                @forelse($paymentsByMethod as $method => $methodPayments)
                    @php
                        $methodTotal = $methodPayments->sum('amount');
                        $percentage = $totalPayments > 0 ? ($methodTotal / $totalPayments) * 100 : 0;
                    @endphp
                    <tr>
                        <td>{{ $method }}</td>
                        <td class="text-center">{{ $methodPayments->count() }}</td>
                        <td class="text-right amount">{{ number_format($methodTotal, 2) }} دينار ليبي</td>
                        <td class="text-center">{{ number_format($percentage, 1) }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">لا توجد مدفوعات</td>
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