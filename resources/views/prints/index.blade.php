@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">مركز الطباعة</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- تقارير مالية -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">التقارير المالية</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <a href="{{ route('print.treasury_report') }}" class="list-group-item list-group-item-action" target="_blank">
                                            <i class="fas fa-vault text-success"></i> تقرير الخزنة العامة
                                        </a>
                                        <a href="{{ route('print.expenses_report') }}" class="list-group-item list-group-item-action" target="_blank">
                                            <i class="fas fa-money-bill-wave text-danger"></i> تقرير المصروفات
                                        </a>
                                        <a href="{{ route('print.revenues_report') }}" class="list-group-item list-group-item-action" target="_blank">
                                            <i class="fas fa-chart-line text-success"></i> تقرير الإيرادات
                                        </a>
                                        <a href="{{ route('print.supplier_payments_report') }}" class="list-group-item list-group-item-action" target="_blank">
                                            <i class="fas fa-hand-holding-usd text-warning"></i> تقرير مدفوعات الموردين
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- إيصالات وسندات -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">الإيصالات والسندات</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <a href="{{ route('receipt_vouchers.index') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-receipt text-success"></i> سندات القبض
                                        </a>
                                        <a href="{{ route('payment_vouchers.index') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-file-invoice-dollar text-danger"></i> سندات الصرف
                                        </a>
                                        <a href="{{ route('purchase_invoices.index') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-file-invoice text-primary"></i> فواتير الشراء
                                        </a>
                                        <a href="{{ route('sales_orders.index') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-shopping-cart text-info"></i> فواتير المبيعات
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- تقارير المخزون -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">تقارير المخزون</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <a href="{{ route('print.inventory_report') }}" class="list-group-item list-group-item-action" target="_blank">
                                            <i class="fas fa-boxes text-info"></i> تقرير المخزون الحالي
                                        </a>
                                        <a href="{{ route('reports.item_movement') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-exchange-alt text-warning"></i> تقرير حركة الأصناف
                                        </a>
                                        <a href="{{ route('reports.warehouse') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-warehouse text-primary"></i> تقارير المخازن
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- تقارير المبيعات -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-white">
                                    <h5 class="mb-0">تقارير المبيعات</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <a href="{{ route('print.sales_report') }}" class="list-group-item list-group-item-action" target="_blank">
                                            <i class="fas fa-chart-bar text-warning"></i> تقرير المبيعات الشامل
                                        </a>
                                        <a href="{{ route('reports.sales') }}" class="list-group-item list-group-item-action">
                                            <i class="fas fa-chart-pie text-success"></i> تقارير المبيعات التفصيلية
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- تعليمات الطباعة -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0">تعليمات الطباعة</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>نصائح للطباعة:</h6>
                                            <ul>
                                                <li>استخدم متصفح Chrome أو Firefox للحصول على أفضل نتائج الطباعة</li>
                                                <li>اضبط إعدادات الطباعة على "A4" للحصول على أفضل تنسيق</li>
                                                <li>اختر "طباعة الخلفية" إذا كنت تريد طباعة الألوان</li>
                                                <li>استخدم "تخطيط الصفحة" للحصول على أفضل عرض</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>ميزات الطباعة:</h6>
                                            <ul>
                                                <li>جميع التقارير تدعم الطباعة المباشرة</li>
                                                <li>التصميم محسن للطباعة مع إخفاء أزرار التنقل</li>
                                                <li>دعم التوقيعات والطوابع الرسمية</li>
                                                <li>إمكانية حفظ التقارير كملفات PDF</li>
                                            </ul>
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
</div>
@endsection 