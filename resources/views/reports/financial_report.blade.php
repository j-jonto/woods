@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">التقرير المالي</h4>
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
                                <a href="{{ route('print.financial_report') }}?from={{ $from }}&to={{ $to }}" class="btn btn-success d-block" target="_blank">
                                    <i class="fas fa-print"></i> طباعة التقرير
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- ملخص مالي -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5>إجمالي المبيعات</h5>
                                    <h3>{{ number_format($financialData['sales'], 2) }} ريال</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5>إجمالي المشتريات</h5>
                                    <h3>{{ number_format($financialData['purchases'], 2) }} ريال</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5>إجمالي المصروفات</h5>
                                    <h3>{{ number_format($financialData['expenses'], 2) }} ريال</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5>إجمالي الإيرادات</h5>
                                    <h3>{{ number_format($financialData['revenues'], 2) }} ريال</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- الأرباح -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">الربح الإجمالي</h5>
                                </div>
                                <div class="card-body text-center">
                                    <h2 class="text-{{ $financialData['gross_profit'] >= 0 ? 'success' : 'danger' }}">
                                        {{ number_format($financialData['gross_profit'], 2) }} ريال
                                    </h2>
                                    <small>المبيعات - المشتريات</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">صافي الربح</h5>
                                </div>
                                <div class="card-body text-center">
                                    <h2 class="text-{{ $financialData['net_profit'] >= 0 ? 'success' : 'danger' }}">
                                        {{ number_format($financialData['net_profit'], 2) }} ريال
                                    </h2>
                                    <small>الربح الإجمالي + الإيرادات - المصروفات</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- نسب مئوية -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">التحليل المالي</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 text-center">
                                            <h4 class="text-success">
                                                {{ $financialData['sales'] > 0 ? number_format(($financialData['gross_profit'] / $financialData['sales']) * 100, 1) : 0 }}%
                                            </h4>
                                            <small>هامش الربح الإجمالي</small>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <h4 class="text-info">
                                                {{ $financialData['sales'] > 0 ? number_format(($financialData['net_profit'] / $financialData['sales']) * 100, 1) : 0 }}%
                                            </h4>
                                            <small>هامش الربح الصافي</small>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <h4 class="text-warning">
                                                {{ $financialData['sales'] > 0 ? number_format(($financialData['expenses'] / $financialData['sales']) * 100, 1) : 0 }}%
                                            </h4>
                                            <small>نسبة المصروفات</small>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <h4 class="text-primary">
                                                {{ $financialData['sales'] > 0 ? number_format(($financialData['revenues'] / $financialData['sales']) * 100, 1) : 0 }}%
                                            </h4>
                                            <small>نسبة الإيرادات الإضافية</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ملاحظات -->
                    <div class="alert alert-info">
                        <h6>ملاحظات:</h6>
                        <ul class="mb-0">
                            <li>الربح الإجمالي = المبيعات - المشتريات</li>
                            <li>صافي الربح = الربح الإجمالي + الإيرادات - المصروفات</li>
                            <li>هامش الربح الإجمالي = (الربح الإجمالي / المبيعات) × 100</li>
                            <li>هامش الربح الصافي = (صافي الربح / المبيعات) × 100</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 