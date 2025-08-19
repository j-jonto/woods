@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">تقرير المصروفات</h4>
            <div>
                <a href="{{ route('print.expenses_report') }}" class="btn btn-light btn-sm" target="_blank">
                    <i class="fas fa-print"></i> طباعة
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- فلاتر البحث -->
            <form method="GET" action="{{ route('reports.expenses') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <label for="expense_type_id" class="form-label">نوع المصروف</label>
                        <select name="expense_type_id" id="expense_type_id" class="form-select">
                            <option value="">جميع الأنواع</option>
                            @foreach($expenseTypes ?? [] as $type)
                                <option value="{{ $type->id }}" {{ request('expense_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
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
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> بحث
                            </button>
                            <a href="{{ route('reports.expenses') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> إلغاء
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- ملخص سريع -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h5>إجمالي المصروفات</h5>
                            <h3>{{ number_format($totalExpenses ?? 0, 2) }} ريال</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h5>عدد المصروفات</h5>
                            <h3>{{ $expensesCount ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h5>متوسط المصروف</h5>
                            <h3>{{ number_format($averageExpense ?? 0, 2) }} ريال</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h5>أعلى مصروف</h5>
                            <h3>{{ number_format($maxExpense ?? 0, 2) }} ريال</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- جدول المصروفات -->
            @if(isset($expenses) && $expenses->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>رقم المصروف</th>
                                <th>نوع المصروف</th>
                                <th>المبلغ</th>
                                <th>التاريخ</th>
                                <th>الوصف</th>
                                <th>المرجع</th>
                                <th>الخزنة</th>
                                <th>أنشئ بواسطة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expenses as $expense)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">{{ $expense->expense_no }}</span>
                                    </td>
                                    <td>
                                        @if($expense->expenseType)
                                            <span class="badge bg-info">{{ $expense->expenseType->name }}</span>
                                        @else
                                            <span class="text-muted">غير محدد</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-danger">
                                            {{ number_format($expense->amount, 2) }} ريال
                                        </span>
                                    </td>
                                    <td>{{ $expense->expense_date }}</td>
                                    <td>
                                        @if($expense->description)
                                            <span title="{{ $expense->description }}">
                                                {{ Str::limit($expense->description, 50) }}
                                            </span>
                                        @else
                                            <span class="text-muted">لا يوجد وصف</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($expense->reference_no)
                                            <small class="text-muted">{{ $expense->reference_no }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($expense->treasury)
                                            <span class="badge bg-success">{{ $expense->treasury->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($expense->creator)
                                            <small>{{ $expense->creator->name }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- ترقيم الصفحات -->
                @if($expenses instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="d-flex justify-content-center mt-4">
                        {{ $expenses->links() }}
                    </div>
                @endif
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i>
                    لا توجد مصروفات لعرضها
                </div>
            @endif

            <!-- تحليل المصروفات حسب النوع -->
            @if(isset($expensesByType) && count($expensesByType) > 0)
                <div class="mt-5">
                    <h5>تحليل المصروفات حسب النوع</h5>
                    <div class="row">
                        @foreach($expensesByType as $type => $amount)
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $type }}</h6>
                                        <p class="card-text text-danger fw-bold">
                                            {{ number_format($amount, 2) }} ريال
                                        </p>
                                        <div class="progress">
                                            <div class="progress-bar bg-danger" 
                                                 style="width: {{ ($amount / ($totalExpenses ?? 1)) * 100 }}%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- ملاحظات -->
            <div class="mt-4">
                <div class="alert alert-light">
                    <h6><i class="fas fa-info-circle"></i> ملاحظات:</h6>
                    <ul class="mb-0">
                        <li>يتم عرض جميع المصروفات المسجلة في النظام</li>
                        <li>يمكن تصفية النتائج حسب النوع والتاريخ</li>
                        <li>المبالغ معروضة بالريال السعودي</li>
                        <li>يمكن طباعة التقرير بالضغط على زر الطباعة</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 