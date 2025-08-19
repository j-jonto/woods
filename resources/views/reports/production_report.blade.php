@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h4 class="mb-0">تقرير الإنتاج</h4>
                </div>
                <div class="card-body">
                    <!-- فلاتر التقرير -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="from" class="form-label">من تاريخ</label>
                                <input type="date" class="form-control" id="from" name="from" value="{{ request('from') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="to" class="form-label">إلى تاريخ</label>
                                <input type="date" class="form-control" id="to" name="to" value="{{ request('to') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block">تطبيق الفلتر</button>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <a href="{{ route('print.production_report') }}?from={{ request('from') }}&to={{ request('to') }}" class="btn btn-success d-block" target="_blank">
                                    <i class="fas fa-print"></i> طباعة التقرير
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- إحصائيات سريعة -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5>إجمالي الأوامر</h5>
                                    <h3>{{ $stats['total_orders'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5>أوامر مكتملة</h5>
                                    <h3>{{ $stats['completed_orders'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5>أوامر قيد التنفيذ</h5>
                                    <h3>{{ $stats['in_progress_orders'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5>أوامر معلقة</h5>
                                    <h3>{{ $stats['pending_orders'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- جدول أوامر الإنتاج -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>رقم الأمر</th>
                                    <th>الصنف</th>
                                    <th>الكمية المطلوبة</th>
                                    <th>الكمية المنتجة</th>
                                    <th>تاريخ البدء</th>
                                    <th>تاريخ الانتهاء</th>
                                    <th>الحالة</th>
                                    <th>نسبة الإنجاز</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productionOrders as $order)
                                    <tr>
                                        <td>{{ $order->order_no }}</td>
                                        <td>{{ $order->item->name ?? 'غير محدد' }}</td>
                                        <td>{{ $order->quantity }}</td>
                                        <td>{{ $order->produced_quantity ?? 0 }}</td>
                                        <td>{{ $order->start_date ? $order->start_date->format('Y-m-d') : '-' }}</td>
                                        <td>{{ $order->completion_date ? $order->completion_date->format('Y-m-d') : '-' }}</td>
                                        <td>
                                            @if($order->status == 'completed')
                                                <span class="badge bg-success">مكتمل</span>
                                            @elseif($order->status == 'in_progress')
                                                <span class="badge bg-warning">قيد التنفيذ</span>
                                            @elseif($order->status == 'pending')
                                                <span class="badge bg-info">معلق</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $order->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $progress = $order->quantity > 0 ? ($order->produced_quantity ?? 0) / $order->quantity * 100 : 0;
                                            @endphp
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%">
                                                    {{ number_format($progress, 1) }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- تحليل الإنتاج -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">توزيع الحالات</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="statusChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">معدل الإنجاز</h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <h2 class="text-success">{{ number_format($stats['total_orders'] > 0 ? ($stats['completed_orders'] / $stats['total_orders']) * 100 : 0, 1) }}%</h2>
                                        <p>نسبة الأوامر المكتملة</p>
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
const ctx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['مكتمل', 'قيد التنفيذ', 'معلق'],
        datasets: [{
            data: [{{ $stats['completed_orders'] }}, {{ $stats['in_progress_orders'] }}, {{ $stats['pending_orders'] }}],
            backgroundColor: ['#28a745', '#ffc107', '#17a2b8']
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