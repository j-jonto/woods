@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">تقرير الخزنة العامة</h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- فلتر التاريخ -->
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">من تاريخ</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate ?? now()->startOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">إلى تاريخ</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate ?? now()->endOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">عرض التقرير</button>
                                <a href="{{ route('print.treasury_report') }}" class="btn btn-success" target="_blank">
                                    <i class="fas fa-print"></i> طباعة
                                </a>
                            </div>
                        </div>
                    </form>

                    @if($treasury)
                        <!-- ملخص التقرير -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h6>إجمالي القبض</h6>
                                        <h4>{{ number_format($receipts ?? 0, 2) }} دينار ليبي</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger text-white">
                                    <div class="card-body text-center">
                                        <h6>إجمالي الصرف</h6>
                                        <h4>{{ number_format($payments ?? 0, 2) }} دينار ليبي</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h6>صافي التغير</h6>
                                        <h4>{{ number_format($netChange ?? 0, 2) }} دينار ليبي</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h6>عدد المعاملات</h6>
                                        <h4>{{ $transactions->count() ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- تفاصيل المعاملات -->
                        <h5>تفاصيل المعاملات</h5>
                        @if($transactions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
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
                                        @foreach($transactions as $transaction)
                                            <tr>
                                                <td>{{ $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : 'غير محدد' }}</td>
                                                <td>
                                                    @if($transaction->type == 'receipt')
                                                        <span class="badge bg-success">قبض</span>
                                                    @elseif($transaction->type == 'payment')
                                                        <span class="badge bg-warning">صرف</span>
                                                    @elseif($transaction->type == 'transfer')
                                                        <span class="badge bg-info">تحويل</span>
                                                    @else
                                                        <span class="badge bg-secondary">تسوية</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    @if($transaction->type == 'receipt')
                                                        <span class="text-success">+{{ number_format($transaction->amount, 2) }}</span>
                                                    @else
                                                        <span class="text-danger">-{{ number_format($transaction->amount, 2) }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">{{ number_format($transaction->balance_after, 2) }} دينار ليبي</td>
                                                <td>{{ $transaction->description }}</td>
                                                <td>{{ $transaction->creator->name ?? 'النظام' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                لا توجد معاملات في الفترة المحددة.
                            </div>
                        @endif

                        <!-- إحصائيات إضافية -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">توزيع المعاملات حسب النوع</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                معاملات القبض
                                                <span class="badge bg-success rounded-pill">{{ $transactions->where('type', 'receipt')->count() }}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                معاملات الصرف
                                                <span class="badge bg-warning rounded-pill">{{ $transactions->where('type', 'payment')->count() }}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                معاملات التحويل
                                                <span class="badge bg-info rounded-pill">{{ $transactions->where('type', 'transfer')->count() }}</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                معاملات التسوية
                                                <span class="badge bg-secondary rounded-pill">{{ $transactions->where('type', 'adjustment')->count() }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">معلومات الخزنة</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                الرصيد الافتتاحي
                                                <span>{{ number_format($treasury->opening_balance, 2) }} دينار ليبي</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                الرصيد الحالي
                                                <span class="fw-bold">{{ number_format($treasury->current_balance, 2) }} دينار ليبي</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                إجمالي القبض
                                                <span class="text-success">{{ number_format($treasury->total_receipts, 2) }} دينار ليبي</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                إجمالي الصرف
                                                <span class="text-danger">{{ number_format($treasury->total_payments, 2) }} دينار ليبي</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <h5>الخزنة العامة غير موجودة</h5>
                            <p>يجب إنشاء الخزنة العامة أولاً.</p>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('treasury.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right"></i> رجوع للخزنة
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-home"></i> لوحة التحكم
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 