@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">تقرير المراجعة</h4>
            <div>
                <a href="{{ route('print.audit_report') }}" class="btn btn-light btn-sm" target="_blank">
                    <i class="fas fa-print"></i> طباعة
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- فلاتر البحث -->
            <form method="GET" action="{{ route('reports.audit') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-2">
                        <label for="user_id" class="form-label">المستخدم</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">جميع المستخدمين</option>
                            @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="action" class="form-label">نوع العملية</label>
                        <select name="action" id="action" class="form-select">
                            <option value="">جميع العمليات</option>
                            <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>إنشاء</option>
                            <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>تحديث</option>
                            <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>حذف</option>
                            <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>تسجيل دخول</option>
                            <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>تسجيل خروج</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="model_type" class="form-label">نوع البيانات</label>
                        <select name="model_type" id="model_type" class="form-select">
                            <option value="">جميع الأنواع</option>
                            <option value="User" {{ request('model_type') == 'User' ? 'selected' : '' }}>المستخدمين</option>
                            <option value="Item" {{ request('model_type') == 'Item' ? 'selected' : '' }}>المنتجات</option>
                            <option value="Customer" {{ request('model_type') == 'Customer' ? 'selected' : '' }}>العملاء</option>
                            <option value="Supplier" {{ request('model_type') == 'Supplier' ? 'selected' : '' }}>الموردين</option>
                            <option value="SalesOrder" {{ request('model_type') == 'SalesOrder' ? 'selected' : '' }}>طلبات البيع</option>
                            <option value="PurchaseOrder" {{ request('model_type') == 'PurchaseOrder' ? 'selected' : '' }}>طلبات الشراء</option>
                            <option value="Expense" {{ request('model_type') == 'Expense' ? 'selected' : '' }}>المصروفات</option>
                            <option value="Revenue" {{ request('model_type') == 'Revenue' ? 'selected' : '' }}>الإيرادات</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">من تاريخ</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">إلى تاريخ</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> بحث
                            </button>
                            <a href="{{ route('reports.audit') }}" class="btn btn-secondary">
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
                            <h5>إجمالي العمليات</h5>
                            <h3>{{ $totalLogs ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h5>عمليات الإنشاء</h5>
                            <h3>{{ $createCount ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h5>عمليات التحديث</h5>
                            <h3>{{ $updateCount ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h5>عمليات الحذف</h5>
                            <h3>{{ $deleteCount ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- جدول سجل المراجعة -->
            @if(isset($auditLogs) && $auditLogs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>التاريخ والوقت</th>
                                <th>المستخدم</th>
                                <th>نوع العملية</th>
                                <th>نوع البيانات</th>
                                <th>معرف البيانات</th>
                                <th>البيانات القديمة</th>
                                <th>البيانات الجديدة</th>
                                <th>عنوان IP</th>
                                <th>المتصفح</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($auditLogs as $log)
                                <tr>
                                    <td>
                                        <small>{{ $log->created_at->format('Y-m-d H:i:s') }}</small>
                                    </td>
                                    <td>
                                        @if($log->user)
                                            <span class="badge bg-info">{{ $log->user->name }}</span>
                                        @else
                                            <span class="text-muted">غير محدد</span>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($log->action)
                                            @case('create')
                                                <span class="badge bg-success">إنشاء</span>
                                                @break
                                            @case('update')
                                                <span class="badge bg-warning">تحديث</span>
                                                @break
                                            @case('delete')
                                                <span class="badge bg-danger">حذف</span>
                                                @break
                                            @case('login')
                                                <span class="badge bg-primary">تسجيل دخول</span>
                                                @break
                                            @case('logout')
                                                <span class="badge bg-secondary">تسجيل خروج</span>
                                                @break
                                            @default
                                                <span class="badge bg-dark">{{ $log->action }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $log->model_type }}</span>
                                    </td>
                                    <td>
                                        <code>{{ $log->model_id }}</code>
                                    </td>
                                    <td>
                                        @if($log->old_values)
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#oldValuesModal{{ $log->id }}">
                                                عرض البيانات القديمة
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->new_values)
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#newValuesModal{{ $log->id }}">
                                                عرض البيانات الجديدة
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $log->ip_address }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ Str::limit($log->user_agent, 30) }}</small>
                                    </td>
                                </tr>

                                <!-- Modal للبيانات القديمة -->
                                @if($log->old_values)
                                    <div class="modal fade" id="oldValuesModal{{ $log->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">البيانات القديمة</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <pre class="bg-light p-3 rounded">{{ json_encode(json_decode($log->old_values), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Modal للبيانات الجديدة -->
                                @if($log->new_values)
                                    <div class="modal fade" id="newValuesModal{{ $log->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">البيانات الجديدة</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <pre class="bg-light p-3 rounded">{{ json_encode(json_decode($log->new_values), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- ترقيم الصفحات -->
                @if($auditLogs instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="d-flex justify-content-center mt-4">
                        {{ $auditLogs->links() }}
                    </div>
                @endif
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i>
                    لا توجد سجلات مراجعة لعرضها
                </div>
            @endif

            <!-- تحليل العمليات حسب النوع -->
            @if(isset($actionsByType) && count($actionsByType) > 0)
                <div class="mt-5">
                    <h5>تحليل العمليات حسب النوع</h5>
                    <div class="row">
                        @foreach($actionsByType as $type => $count)
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">{{ $type }}</h6>
                                        <p class="card-text fw-bold text-primary">{{ $count }}</p>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary" 
                                                 style="width: {{ ($count / ($totalLogs ?? 1)) * 100 }}%">
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
                        <li>يتم تسجيل جميع العمليات التي يقوم بها المستخدمون في النظام</li>
                        <li>يمكن تصفية النتائج حسب المستخدم ونوع العملية والتاريخ</li>
                        <li>البيانات القديمة والجديدة متاحة للعرض عند التحديث</li>
                        <li>يمكن طباعة التقرير بالضغط على زر الطباعة</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 