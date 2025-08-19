@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">الخزنة العامة</h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($treasury)
                        <!-- ملخص الخزنة -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5>الرصيد الحالي</h5>
                                        <h3>{{ number_format($treasury->current_balance, 2) }} دينار ليبي</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h5>إجمالي القبض</h5>
                                        <h3>{{ number_format($treasury->total_receipts, 2) }} دينار ليبي</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h5>إجمالي الصرف</h5>
                                        <h3>{{ number_format($treasury->total_payments, 2) }} دينار ليبي</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-secondary text-white">
                                    <div class="card-body text-center">
                                        <h5>صافي التغير</h5>
                                        <h3>{{ number_format($treasury->total_receipts - $treasury->total_payments, 2) }} دينار ليبي</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- أزرار الإجراءات -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addReceiptModal">
                                    <i class="fas fa-plus"></i> إضافة قبض
                                </button>
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                                    <i class="fas fa-minus"></i> إضافة صرف
                                </button>
                                <a href="{{ route('treasury.show', $treasury->id) }}" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> عرض التفاصيل
                                </a>
                                <a href="{{ route('treasury.edit', $treasury->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> تعديل الخزنة
                                </a>
                                <a href="{{ route('treasury.report') }}" class="btn btn-info">
                                    <i class="fas fa-chart-bar"></i> تقرير الخزنة
                                </a>
                                <a href="{{ route('print.treasury_report') }}" class="btn btn-success" target="_blank">
                                    <i class="fas fa-print"></i> طباعة التقرير
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <h5>الخزنة العامة غير موجودة</h5>
                            <p>يجب إنشاء الخزنة العامة أولاً.</p>
                        </div>
                    @endif

                    <!-- جدول المعاملات -->
                    @if($transactions && $transactions->count() > 0)
                        <h5>آخر المعاملات</h5>
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
                                            <td>{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                                            <td>
                                                @if($transaction->type == 'receipt')
                                                    <span class="badge bg-success">قبض</span>
                                                @else
                                                    <span class="badge bg-warning">صرف</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($transaction->amount, 2) }} دينار ليبي</td>
                                            <td>{{ number_format($transaction->balance_after, 2) }} دينار ليبي</td>
                                            <td>{{ $transaction->description }}</td>
                                            <td>{{ $transaction->creator->name ?? 'غير محدد' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            {{ $transactions->links() }}
                        @endif
                    @else
                        <div class="alert alert-info">
                            لا توجد معاملات في الخزنة العامة.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal إضافة قبض -->
<div class="modal fade" id="addReceiptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة قبض</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('treasury.add_receipt') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">المبلغ</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">الوصف</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="transaction_date" class="form-label">التاريخ</label>
                        <input type="date" class="form-control" id="transaction_date" name="transaction_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">إضافة القبض</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal إضافة صرف -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة صرف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('treasury.add_payment') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="payment_amount" class="form-label">المبلغ</label>
                        <input type="number" step="0.01" class="form-control" id="payment_amount" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_description" class="form-label">الوصف</label>
                        <textarea class="form-control" id="payment_description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">التاريخ</label>
                        <input type="date" class="form-control" id="payment_date" name="transaction_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning">إضافة الصرف</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 