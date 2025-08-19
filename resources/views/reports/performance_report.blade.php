@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">تقرير الأداء</h4>
            <div>
                <a href="{{ route('print.performance_report') }}" class="btn btn-light btn-sm" target="_blank">
                    <i class="fas fa-print"></i> طباعة
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- فلاتر البحث -->
            <form method="GET" action="{{ route('reports.performance') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">من تاريخ</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">إلى تاريخ</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="metric" class="form-label">المقياس</label>
                        <select name="metric" id="metric" class="form-select">
                            <option value="">جميع المقاييس</option>
                            <option value="sales" {{ request('metric') == 'sales' ? 'selected' : '' }}>المبيعات</option>
                            <option value="purchases" {{ request('metric') == 'purchases' ? 'selected' : '' }}>المشتريات</option>
                            <option value="production" {{ request('metric') == 'production' ? 'selected' : '' }}>الإنتاج</option>
                            <option value="inventory" {{ request('metric') == 'inventory' ? 'selected' : '' }}>المخزون</option>
                            <option value="financial" {{ request('metric') == 'financial' ? 'selected' : '' }}>المالية</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> بحث
                            </button>
                            <a href="{{ route('reports.performance') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> إلغاء
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- مؤشرات الأداء الرئيسية -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h5>إجمالي المبيعات</h5>
                            <h3>{{ number_format($totalSales ?? 0, 2) }} ريال</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h5>إجمالي المشتريات</h5>
                            <h3>{{ number_format($totalPurchases ?? 0, 2) }} ريال</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h5>إجمالي الإيرادات</h5>
                            <h3>{{ number_format($totalRevenues ?? 0, 2) }} ريال</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h5>إجمالي المصروفات</h5>
                            <h3>{{ number_format($totalExpenses ?? 0, 2) }} ريال</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- مؤشرات الأداء الإضافية -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h5>صافي الربح</h5>
                            <h3>{{ number_format($netProfit ?? 0, 2) }} ريال</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h5>عدد الطلبات</h5>
                            <h3>{{ $ordersCount ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h5>عدد المنتجات</h5>
                            <h3>{{ $itemsCount ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h5>المنتجات منخفضة المخزون</h5>
                            <h3>{{ $lowStockItems ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- تحليل الأداء الشهري -->
            @if(isset($monthlyData) && count($monthlyData) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>تحليل الأداء الشهري</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>الشهر</th>
                                        <th>المبيعات</th>
                                        <th>المشتريات</th>
                                        <th>الإيرادات</th>
                                        <th>المصروفات</th>
                                        <th>صافي الربح</th>
                                        <th>عدد الطلبات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monthlyData as $month => $data)
                                        <tr>
                                            <td><strong>{{ $month }}</strong></td>
                                            <td class="text-primary">{{ number_format($data['sales'] ?? 0, 2) }} ريال</td>
                                            <td class="text-success">{{ number_format($data['purchases'] ?? 0, 2) }} ريال</td>
                                            <td class="text-info">{{ number_format($data['revenues'] ?? 0, 2) }} ريال</td>
                                            <td class="text-warning">{{ number_format($data['expenses'] ?? 0, 2) }} ريال</td>
                                            <td class="text-{{ ($data['profit'] ?? 0) >= 0 ? 'success' : 'danger' }}">
                                                {{ number_format($data['profit'] ?? 0, 2) }} ريال
                                            </td>
                                            <td>{{ $data['orders'] ?? 0 }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- تحليل المنتجات الأكثر مبيعاً -->
            @if(isset($topSellingItems) && count($topSellingItems) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>المنتجات الأكثر مبيعاً</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($topSellingItems as $item)
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $item->name }}</h6>
                                            <p class="card-text">
                                                <span class="text-primary">الكمية المباعة: {{ $item->total_quantity }}</span><br>
                                                <span class="text-success">إجمالي المبيعات: {{ number_format($item->total_sales, 2) }} ريال</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- تحليل العملاء الأكثر نشاطاً -->
            @if(isset($topCustomers) && count($topCustomers) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>العملاء الأكثر نشاطاً</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>العميل</th>
                                        <th>عدد الطلبات</th>
                                        <th>إجمالي المشتريات</th>
                                        <th>متوسط قيمة الطلب</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topCustomers as $customer)
                                        <tr>
                                            <td><strong>{{ $customer->name }}</strong></td>
                                            <td>{{ $customer->orders_count }}</td>
                                            <td class="text-success">{{ number_format($customer->total_purchases, 2) }} ريال</td>
                                            <td class="text-info">{{ number_format($customer->average_order_value, 2) }} ريال</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- مؤشرات الأداء المالية -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6>مؤشرات الربحية</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">معدل الربح الإجمالي</label>
                                <div class="progress">
                                    <div class="progress-bar bg-success" 
                                         style="width: {{ $grossProfitMargin ?? 0 }}%">
                                        {{ number_format($grossProfitMargin ?? 0, 1) }}%
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">معدل الربح الصافي</label>
                                <div class="progress">
                                    <div class="progress-bar bg-primary" 
                                         style="width: {{ $netProfitMargin ?? 0 }}%">
                                        {{ number_format($netProfitMargin ?? 0, 1) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6>مؤشرات الكفاءة</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">معدل دوران المخزون</label>
                                <div class="progress">
                                    <div class="progress-bar bg-info" 
                                         style="width: {{ min(($inventoryTurnover ?? 0) * 10, 100) }}%">
                                        {{ number_format($inventoryTurnover ?? 0, 2) }}
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">متوسط قيمة الطلب</label>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" 
                                         style="width: {{ min(($averageOrderValue ?? 0) / 1000 * 100, 100) }}%">
                                        {{ number_format($averageOrderValue ?? 0, 2) }} ريال
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ملاحظات -->
            <div class="mt-4">
                <div class="alert alert-light">
                    <h6><i class="fas fa-info-circle"></i> ملاحظات:</h6>
                    <ul class="mb-0">
                        <li>يتم عرض مؤشرات الأداء الرئيسية للنظام</li>
                        <li>يمكن تصفية النتائج حسب الفترة الزمنية والمقياس</li>
                        <li>المؤشرات المالية تساعد في تقييم أداء الأعمال</li>
                        <li>يمكن طباعة التقرير بالضغط على زر الطباعة</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 