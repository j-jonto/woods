@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">تقرير الإيرادات</h4>
            <div>
                <a href="{{ route('print.revenues_report') }}" class="btn btn-light btn-sm" target="_blank">
                    <i class="fas fa-print"></i> طباعة
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- فلاتر البحث -->
            <form method="GET" action="{{ route('reports.revenues') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <label for="revenue_type_id" class="form-label">نوع الإيراد</label>
                        <select name="revenue_type_id" id="revenue_type_id" class="form-select">
                            <option value="">جميع الأنواع</option>
                            @foreach($revenueTypes ?? [] as $type)
                                <option value="{{ $type->id }}" {{ request('revenue_type_id') == $type->id ? 'selected' : '' }}>
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
                            <a href="{{ route('reports.revenues') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> إلغاء
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- ملخص سريع -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h5>إجمالي الإيرادات</h5>
                            <h3>{{ number_format($totalRevenues ?? 0, 2) }} ريال</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h5>عدد الإيرادات</h5>
                            <h3>{{ $revenuesCount ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h5>متوسط الإيراد</h5>
                            <h3>{{ number_format($averageRevenue ?? 0, 2) }} ريال</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h5>أعلى إيراد</h5>
                            <h3>{{ number_format($maxRevenue ?? 0, 2) }} ريال</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- جدول الإيرادات -->
            @if(isset($revenues) && $revenues->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>رقم الإيراد</th>
                                <th>نوع الإيراد</th>
                                <th>المبلغ</th>
                                <th>التاريخ</th>
                                <th>الوصف</th>
                                <th>المرجع</th>
                                <th>الخزنة</th>
                                <th>أنشئ بواسطة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($revenues as $revenue)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">{{ $revenue->revenue_no }}</span>
                                    </td>
                                    <td>
                                        @if($revenue->revenueType)
                                            <span class="badge bg-success">{{ $revenue->revenueType->name }}</span>
                                        @else
                                            <span class="text-muted">غير محدد</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">
                                            {{ number_format($revenue->amount, 2) }} ريال
                                        </span>
                                    </td>
                                    <td>{{ $revenue->revenue_date }}</td>
                                    <td>
                                        @if($revenue->description)
                                            <span title="{{ $revenue->description }}">
                                                {{ Str::limit($revenue->description, 50) }}
                                            </span>
                                        @else
                                            <span class="text-muted">لا يوجد وصف</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($revenue->reference_no)
                                            <small class="text-muted">{{ $revenue->reference_no }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($revenue->treasury)
                                            <span class="badge bg-success">{{ $revenue->treasury->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($revenue->creator)
                                            <small>{{ $revenue->creator->name }}</small>
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
                @if($revenues instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="d-flex justify-content-center mt-4">
                        {{ $revenues->links() }}
                    </div>
                @endif
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i>
                    لا توجد إيرادات لعرضها
                </div>
            @endif

            <!-- تحليل الإيرادات حسب النوع -->
            @if(isset($revenuesByType) && count($revenuesByType) > 0)
                <div class="mt-5">
                    <h5>تحليل الإيرادات حسب النوع</h5>
                    <div class="row">
                        @foreach($revenuesByType as $type => $amount)
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $type }}</h6>
                                        <p class="card-text text-success fw-bold">
                                            {{ number_format($amount, 2) }} ريال
                                        </p>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" 
                                                 style="width: {{ ($amount / ($totalRevenues ?? 1)) * 100 }}%">
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
                        <li>يتم عرض جميع الإيرادات المسجلة في النظام</li>
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