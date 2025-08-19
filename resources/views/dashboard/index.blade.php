@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- الروابط السريعة -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">الروابط السريعة</h6>
                </div>
                <div class="card-body">
                    <!-- الصف الأول - العمليات الأساسية -->
                    <div class="row mb-3">
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('sales_orders.index') }}" class="btn btn-primary btn-block w-100">
                                <i class="fas fa-shopping-cart"></i><br>المبيعات
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('purchase_orders.index') }}" class="btn btn-success btn-block w-100">
                                <i class="fas fa-truck"></i><br>المشتريات
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('inventory_transactions.index') }}" class="btn btn-info btn-block w-100">
                                <i class="fas fa-boxes"></i><br>المخزن
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('warehouses.index') }}" class="btn btn-secondary btn-block w-100">
                                <i class="fas fa-warehouse"></i><br>المستودعات
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('production_orders.index') }}" class="btn btn-dark btn-block w-100">
                                <i class="fas fa-industry"></i><br>الإنتاج
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('treasury.index') }}" class="btn btn-warning btn-block w-100">
                                <i class="fas fa-vault"></i><br>الخزنة
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('expenses.index') }}" class="btn btn-danger btn-block w-100">
                                <i class="fas fa-money-bill-wave"></i><br>المصروفات
                            </a>
                        </div>
                    </div>
                    
                    <!-- الصف الثاني - التقارير -->
                    <div class="row mb-3">
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('reports.sales') }}" class="btn btn-outline-primary btn-block w-100">
                                <i class="fas fa-chart-bar"></i><br>تقرير المبيعات
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('reports.purchases') }}" class="btn btn-outline-success btn-block w-100">
                                <i class="fas fa-chart-pie"></i><br>تقرير المشتريات
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('reports.inventory') }}" class="btn btn-outline-info btn-block w-100">
                                <i class="fas fa-boxes"></i><br>تقرير المخزون
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('reports.financial') }}" class="btn btn-outline-warning btn-block w-100">
                                <i class="fas fa-file-invoice-dollar"></i><br>التقرير المالي
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('reports.expenses') }}" class="btn btn-outline-danger btn-block w-100">
                                <i class="fas fa-file-alt"></i><br>تقرير المصروفات
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('reports.audit') }}" class="btn btn-outline-dark btn-block w-100">
                                <i class="fas fa-shield-alt"></i><br>تقرير المراجعة
                            </a>
                        </div>
                    </div>
                    
                    <!-- الصف الثالث - العمليات الإضافية -->
                    <div class="row mb-3">
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('revenues.index') }}" class="btn btn-outline-success btn-block w-100">
                                <i class="fas fa-chart-line"></i><br>الإيرادات
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('reports.revenues') }}" class="btn btn-outline-success btn-block w-100">
                                <i class="fas fa-file-chart-line"></i><br>تقرير الإيرادات
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('reports.treasury') }}" class="btn btn-outline-warning btn-block w-100">
                                <i class="fas fa-vault"></i><br>تقرير الخزنة
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('reports.production') }}" class="btn btn-outline-secondary btn-block w-100">
                                <i class="fas fa-industry"></i><br>تقرير الإنتاج
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('reports.performance') }}" class="btn btn-outline-info btn-block w-100">
                                <i class="fas fa-tachometer-alt"></i><br>تقرير الأداء
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('audit_logs.index') }}" class="btn btn-outline-dark btn-block w-100">
                                <i class="fas fa-history"></i><br>سجل المراجعة
                            </a>
                        </div>
                    </div>
                    
                    <!-- الصف الرابع - إدارة البيانات -->
                    <div class="row">
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-block w-100">
                                <i class="fas fa-users"></i><br>العملاء
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary btn-block w-100">
                                <i class="fas fa-truck-loading"></i><br>الموردين
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-block w-100">
                                <i class="fas fa-user-cog"></i><br>المستخدمين
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('item_categories.index') }}" class="btn btn-outline-secondary btn-block w-100">
                                <i class="fas fa-tags"></i><br>فئات المنتجات
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('work_centers.index') }}" class="btn btn-outline-secondary btn-block w-100">
                                <i class="fas fa-cogs"></i><br>مراكز العمل
                            </a>
                        </div>
                        <div class="col-md-2 mb-2">
                            <a href="{{ route('audit_logs.index') }}" class="btn btn-outline-secondary btn-block w-100">
                                <i class="fas fa-history"></i><br>سجل المراجعة
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- التنبيهات العاجلة -->
    @if(count($alerts) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-exclamation-triangle"></i>
                    تنبيهات النظام
                </h5>
                <div class="row">
                    @foreach($alerts as $alert)
                    <div class="col-md-4 mb-2">
                        <div class="alert alert-{{ $alert['type'] }} mb-0">
                            <i class="{{ $alert['icon'] }}"></i>
                            <strong>{{ $alert['title'] }}:</strong> {{ $alert['message'] }}
                        </div>
                    </div>
                    @endforeach
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    @endif

    <!-- الإحصائيات السريعة -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                إجمالي المبيعات
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalSales, 2) }} ر.س
                            </div>
                            @if(isset($monthlyStats['sales_growth']))
                            <small class="text-{{ $monthlyStats['sales_growth'] >= 0 ? 'success' : 'danger' }}">
                                <i class="fas fa-arrow-{{ $monthlyStats['sales_growth'] >= 0 ? 'up' : 'down' }}"></i>
                                {{ number_format(abs($monthlyStats['sales_growth']), 1) }}%
                            </small>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                إجمالي المشتريات
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalPurchases, 2) }} ر.س
                            </div>
                            @if(isset($monthlyStats['purchases_growth']))
                            <small class="text-{{ $monthlyStats['purchases_growth'] >= 0 ? 'success' : 'danger' }}">
                                <i class="fas fa-arrow-{{ $monthlyStats['purchases_growth'] >= 0 ? 'up' : 'down' }}"></i>
                                {{ number_format(abs($monthlyStats['purchases_growth']), 1) }}%
                            </small>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                رصيد الخزنة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($treasuryBalance, 2) }} ر.س
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-vault fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                الإنتاج الجاري
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $productionInProgress }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-industry fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- الرسوم البيانية -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">تحليل المبيعات والمشتريات</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <a class="dropdown-item" href="#" onclick="updateChart('sales')">المبيعات</a>
                            <a class="dropdown-item" href="#" onclick="updateChart('purchases')">المشتريات</a>
                            <a class="dropdown-item" href="#" onclick="updateChart('both')">كلاهما</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">توزيع المخزون</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="inventoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- إحصائيات الفروع والعملات -->
    <div class="row mb-4">
        @if(count($branchStats) > 0)
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">إحصائيات الفروع</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>الفرع</th>
                                    <th>المبيعات</th>
                                    <th>المشتريات</th>
                                    <th>العملاء</th>
                                    <th>رصيد الخزنة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branchStats as $branch)
                                <tr>
                                    <td>{{ $branch['name'] }}</td>
                                    <td>{{ number_format($branch['sales'], 2) }} ر.س</td>
                                    <td>{{ number_format($branch['purchases'], 2) }} ر.س</td>
                                    <td>{{ $branch['customers'] }}</td>
                                    <td>{{ number_format($branch['treasury_balance'], 2) }} ر.س</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(count($currencyStats) > 0)
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">إحصائيات العملات</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>العملة</th>
                                    <th>الرمز</th>
                                    <th>سعر الصرف</th>
                                    <th>عدد المعاملات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($currencyStats as $currency)
                                <tr>
                                    <td>{{ $currency['name'] }}</td>
                                    <td>{{ $currency['symbol'] }}</td>
                                    <td>{{ number_format($currency['exchange_rate'], 6) }}</td>
                                    <td>{{ $currency['total_transactions'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- إحصائيات التدقيق -->
    @if(isset($auditStats))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">إحصائيات التدقيق والمراجعة</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 text-center">
                            <div class="border rounded p-3">
                                <h4 class="text-primary">{{ $auditStats['today_events'] }}</h4>
                                <small>أحداث اليوم</small>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="border rounded p-3">
                                <h4 class="text-info">{{ $auditStats['week_events'] }}</h4>
                                <small>أحداث الأسبوع</small>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="border rounded p-3">
                                <h4 class="text-success">{{ $auditStats['month_events'] }}</h4>
                                <small>أحداث الشهر</small>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="border rounded p-3">
                                <h4 class="text-warning">{{ $auditStats['unresolved_alerts'] }}</h4>
                                <small>تنبيهات غير محلولة</small>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="border rounded p-3">
                                <h4 class="text-danger">{{ $auditStats['critical_alerts'] }}</h4>
                                <small>تنبيهات حرجة</small>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="border rounded p-3">
                                <h4 class="text-dark">{{ $auditStats['security_alerts'] }}</h4>
                                <small>تنبيهات أمنية</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- المعاملات الحديثة -->
    <div class="row mb-4">
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">آخر المبيعات</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>رقم الطلب</th>
                                    <th>العميل</th>
                                    <th>المبلغ</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentSales as $sale)
                                <tr>
                                    <td>{{ $sale->order_no }}</td>
                                    <td>{{ $sale->customer ? $sale->customer->name : 'غير محدد' }}</td>
                                    <td>{{ number_format($sale->total_amount, 2) }} ر.س</td>
                                    <td>{{ $sale->order_date->format('Y-m-d') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">آخر المشتريات</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>رقم الطلب</th>
                                    <th>المورد</th>
                                    <th>المبلغ</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPurchases as $purchase)
                                <tr>
                                    <td>{{ $purchase->order_no }}</td>
                                    <td>{{ $purchase->supplier ? $purchase->supplier->name : 'غير محدد' }}</td>
                                    <td>{{ number_format($purchase->total_amount, 2) }} ر.س</td>
                                    <td>{{ $purchase->order_date->format('Y-m-d') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// رسم بياني للمبيعات
const salesCtx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: @json(array_column($salesChart, 'month')),
        datasets: [{
            label: 'المبيعات',
            data: @json(array_column($salesChart, 'sales')),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'المشتريات',
            data: @json(array_column($purchasesChart, 'purchases')),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'تحليل المبيعات والمشتريات الشهري'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// رسم بياني للمخزون
const inventoryCtx = document.getElementById('inventoryChart').getContext('2d');
const inventoryChart = new Chart(inventoryCtx, {
    type: 'doughnut',
    data: {
        labels: @json(array_column($inventoryChart, 'item')),
        datasets: [{
            data: @json(array_column($inventoryChart, 'stock')),
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 205, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            },
            title: {
                display: true,
                text: 'توزيع المخزون'
            }
        }
    }
});

// تحديث الرسم البياني
function updateChart(type) {
    fetch(`/dashboard/chart-data?type=${type}`)
        .then(response => response.json())
        .then(data => {
            // تحديث البيانات
            salesChart.data.labels = data.map(item => item.month);
            if (type === 'sales' || type === 'both') {
                salesChart.data.datasets[0].data = data.map(item => item.sales);
            }
            if (type === 'purchases' || type === 'both') {
                salesChart.data.datasets[1].data = data.map(item => item.purchases);
            }
            salesChart.update();
        });
}

// تحديث التنبيهات كل 30 ثانية
setInterval(function() {
    fetch('/dashboard/alerts')
        .then(response => response.json())
        .then(alerts => {
            // تحديث التنبيهات في الواجهة
            console.log('Updated alerts:', alerts);
        });
}, 30000);

// تحديث الإحصائيات كل دقيقة
setInterval(function() {
    fetch('/dashboard/stats')
        .then(response => response.json())
        .then(stats => {
            // تحديث الإحصائيات في الواجهة
            console.log('Updated stats:', stats);
        });
}, 60000);
</script>
@endsection 