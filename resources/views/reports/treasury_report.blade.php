@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h4 class="mb-0">تقرير الخزنة</h4>
                </div>
                <div class="card-body">
                    <!-- فلاتر التقرير -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="from" class="form-label">من تاريخ</label>
                                <input type="date" class="form-control" id="from" name="from" value="{{ $from }}">
                            </div>
                            <div class="col-md-3">
                                <label for="to" class="form-label">إلى تاريخ</label>
                                <input type="date" class="form-control" id="to" name="to" value="{{ $to }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block">تطبيق الفلتر</button>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <a href="{{ route('print.treasury_report') }}?from={{ $from }}&to={{ $to }}" class="btn btn-success d-block" target="_blank">
                                    <i class="fas fa-print"></i> طباعة التقرير
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- ملخص الخزنة -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5>إجمالي القبض</h5>
                                    <h3>{{ number_format($summary['total_receipts'], 2) }} ريال</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5>إجمالي الصرف</h5>
                                    <h3>{{ number_format($summary['total_payments'], 2) }} ريال</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5>صافي التغير</h5>
                                    <h3>{{ number_format($summary['net_balance'], 2) }} ريال</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5>عدد المعاملات</h5>
                                    <h3>{{ $transactions->count() }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- جدول المعاملات -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th>النوع</th>
                                    <th>المبلغ</th>
                                    <th>الوصف</th>
                                    <th>الرصيد بعد المعاملة</th>
                                    <th>المستخدم</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                                        <td>
                                            @if($transaction->type == 'receipt')
                                                <span class="badge bg-success">قبض</span>
                                            @else
                                                <span class="badge bg-warning">صرف</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($transaction->amount, 2) }} ريال</td>
                                        <td>{{ $transaction->description }}</td>
                                        <td>{{ number_format($transaction->balance_after, 2) }} ريال</td>
                                        <td>{{ $transaction->creator->name ?? 'غير محدد' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- تحليل الخزنة -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">توزيع المعاملات</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="transactionChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">تحليل التدفق النقدي</h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <h2 class="text-{{ $summary['net_balance'] >= 0 ? 'success' : 'danger' }}">
                                            {{ number_format($summary['net_balance'], 2) }} ريال
                                        </h2>
                                        <p>صافي التدفق النقدي</p>
                                        
                                        @if($summary['total_receipts'] > 0)
                                            <div class="mt-3">
                                                <small>نسبة القبض: {{ number_format(($summary['total_receipts'] / ($summary['total_receipts'] + $summary['total_payments'])) * 100, 1) }}%</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('transactionChart').getContext('2d');
const transactionChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['قبض', 'صرف'],
        datasets: [{
            data: [{{ $summary['total_receipts'] }}, {{ $summary['total_payments'] }}],
            backgroundColor: ['#28a745', '#ffc107']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});
</script>
@endsection 