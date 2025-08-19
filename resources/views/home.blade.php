@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <div class="p-5 bg-light rounded shadow-lg mb-4">
                <i class="fas fa-industry fa-5x text-primary mb-3"></i>
                <h1 class="display-4 mb-3">نظام إدارة المصانع المتكامل</h1>
                <p class="lead">نظام متكامل لإدارة عمليات التصنيع والإنتاج بكفاءة عالية</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card h-100 border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-industry fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">إدارة الإنتاج</h5>
                    <p class="card-text">إدارة أوامر الإنتاج ومراكز العمل وخطوط الإنتاج</p>
                    <a href="{{ route('production_orders.index') }}" class="btn btn-primary">عرض التفاصيل</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card h-100 border-success">
                <div class="card-body text-center">
                    <i class="fas fa-boxes fa-3x text-success mb-3"></i>
                    <h5 class="card-title">إدارة المخزون</h5>
                    <p class="card-text">متابعة المخزون والمواد الخام والمنتجات النهائية</p>
                    <a href="{{ route('inventory.index') }}" class="btn btn-success">عرض التفاصيل</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card h-100 border-info">
                <div class="card-body text-center">
                    <i class="fas fa-cogs fa-3x text-info mb-3"></i>
                    <h5 class="card-title">قوائم المواد</h5>
                    <p class="card-text">إدارة قوائم المواد والمكونات والمواد الخام</p>
                    <a href="{{ route('bill_of_materials.index') }}" class="btn btn-info">عرض التفاصيل</a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card h-100 border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">التقارير</h5>
                    <p class="card-text">تقارير الإنتاج والمخزون والكفاءة</p>
                    <a href="{{ route('reports.index') }}" class="btn btn-warning">عرض التفاصيل</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">مؤشرات الأداء الرئيسية</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>كفاءة الإنتاج</span>
                                <span class="badge bg-success">85%</span>
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 85%"></div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>معدل الإنتاج</span>
                                <span class="badge bg-info">92%</span>
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 92%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">أحدث أوامر الإنتاج</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>رقم الأمر</th>
                                    <th>المنتج</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($latestProductionOrders) && count($latestProductionOrders) > 0)
                                    @foreach($latestProductionOrders as $order)
                                    <tr>
                                        <td>{{ $order->order_no }}</td>
                                        <td>{{ $order->product->name }}</td>
                                        <td>
                                            <span class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'in_progress' ? 'warning' : 'secondary') }}">
                                                {{ $order->status == 'completed' ? 'مكتمل' : ($order->status == 'in_progress' ? 'قيد التنفيذ' : 'قيد الانتظار') }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center">لا توجد أوامر إنتاج حديثة</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- قسم روابط التقارير الجديدة -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">التقارير والتحليلات</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-exchange-alt fa-2x text-info mb-2"></i>
                                    <h6 class="card-title">تتبع حركة الصنف</h6>
                                    <p class="card-text">عرض جميع الحركات (إدخال، إخراج، تحويل، تسوية) لكل صنف مع خيارات التصفية.</p>
                                    <a href="{{ route('reports.item_movement') }}" class="btn btn-info">عرض التقرير</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-warehouse fa-2x text-success mb-2"></i>
                                    <h6 class="card-title">تقارير المخزن</h6>
                                    <p class="card-text">تقرير الكميات المتوفرة، الأصناف الناقصة، وحركة المخزون خلال فترة.</p>
                                    <a href="{{ route('reports.warehouse') }}" class="btn btn-success">عرض التقرير</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-invoice-dollar fa-2x text-primary mb-2"></i>
                                    <h6 class="card-title">تقارير المبيعات</h6>
                                    <p class="card-text">تقرير مبيعات إجمالي وتفصيلي مع خيارات التصفية حسب الصنف والعميل والفترة.</p>
                                    <a href="{{ route('reports.sales') }}" class="btn btn-primary">عرض التقرير</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- قسم روابط الوحدات المالية الجديدة -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">الوحدات المالية والإدارية</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <i class="fas fa-money-bill-wave fa-2x text-primary mb-2"></i>
                                    <h6 class="card-title">المصروفات</h6>
                                    <p class="card-text">إدارة وتسجيل جميع المصروفات بأنواعها المختلفة.</p>
                                    <a href="{{ route('expenses.index') }}" class="btn btn-primary">عرض المصروفات</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <i class="fas fa-coins fa-2x text-success mb-2"></i>
                                    <h6 class="card-title">الإيرادات</h6>
                                    <p class="card-text">تسجيل ومتابعة جميع الإيرادات الأخرى.</p>
                                    <a href="{{ route('revenues.index') }}" class="btn btn-success">عرض الإيرادات</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 border-info">
                                <div class="card-body text-center">
                                    <i class="fas fa-cash-register fa-2x text-info mb-2"></i>
                                    <h6 class="card-title">حسابات الصندوق والبنك</h6>
                                    <p class="card-text">إدارة الصناديق والبنوك وسندات القبض والصرف.</p>
                                    <a href="{{ route('cash_accounts.index') }}" class="btn btn-info">عرض الحسابات</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 border-warning">
                                <div class="card-body text-center">
                                    <i class="fas fa-hand-holding-usd fa-2x text-warning mb-2"></i>
                                    <h6 class="card-title">مدفوعات الموردين</h6>
                                    <p class="card-text">تسجيل ومتابعة جميع مدفوعات الموردين.</p>
                                    <a href="{{ route('supplier_payments.index') }}" class="btn btn-warning">عرض المدفوعات</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .progress {
        height: 10px;
    }
    .bg-light {
        background-color: #f8f9fa !important;
    }
</style>
@endpush
@endsection
