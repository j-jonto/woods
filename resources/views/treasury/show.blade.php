@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">تفاصيل الخزنة العامة</h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <h5>معلومات الخزنة</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <td><strong>اسم الخزنة:</strong></td>
                                    <td>{{ $treasury->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>الرصيد الافتتاحي:</strong></td>
                                    <td>{{ number_format($treasury->opening_balance, 2) }} دينار ليبي</td>
                                </tr>
                                <tr>
                                    <td><strong>الرصيد الحالي:</strong></td>
                                    <td class="text-success fw-bold">{{ number_format($treasury->current_balance, 2) }} دينار ليبي</td>
                                </tr>
                                <tr>
                                    <td><strong>إجمالي القبض:</strong></td>
                                    <td class="text-success">{{ number_format($treasury->total_receipts, 2) }} دينار ليبي</td>
                                </tr>
                                <tr>
                                    <td><strong>إجمالي الصرف:</strong></td>
                                    <td class="text-danger">{{ number_format($treasury->total_payments, 2) }} دينار ليبي</td>
                                </tr>
                                <tr>
                                    <td><strong>الحالة:</strong></td>
                                    <td>
                                        @if($treasury->is_active)
                                            <span class="badge bg-success">نشط</span>
                                        @else
                                            <span class="badge bg-danger">غير نشط</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>الوصف:</strong></td>
                                    <td>{{ $treasury->description ?? 'لا يوجد وصف' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>تاريخ الإنشاء:</strong></td>
                                    <td>{{ $treasury->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>آخر تحديث:</strong></td>
                                    <td>{{ $treasury->updated_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>ملخص سريع</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h6>صافي التغير</h6>
                                            <h4>{{ number_format($treasury->total_receipts - $treasury->total_payments, 2) }} دينار ليبي</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h6>عدد المعاملات</h6>
                                            <h4>{{ $treasury->transactions->count() }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <h6>آخر المعاملات</h6>
                                @if($treasury->transactions->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>التاريخ</th>
                                                    <th>النوع</th>
                                                    <th>المبلغ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($treasury->transactions->take(5) as $transaction)
                                                    <tr>
                                                        <td>{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                                                        <td>
                                                            @if($transaction->type == 'receipt')
                                                                <span class="badge bg-success">قبض</span>
                                                            @else
                                                                <span class="badge bg-warning">صرف</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ number_format($transaction->amount, 2) }} دينار ليبي</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted">لا توجد معاملات</p>
                                @endif
                            </div>
                        </div>
                    </div>

                                                    <div class="mt-4">
                                    <a href="{{ route('treasury.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-right"></i> رجوع
                                    </a>
                                    <a href="{{ route('treasury.edit', $treasury->id) }}" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> تعديل
                                    </a>
                                    <a href="{{ route('treasury.report') }}" class="btn btn-info">
                                        <i class="fas fa-chart-bar"></i> تقرير مفصل
                                    </a>
                                    <a href="{{ route('print.treasury_report') }}" class="btn btn-success" target="_blank">
                                        <i class="fas fa-print"></i> طباعة التقرير
                                    </a>
                                </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 