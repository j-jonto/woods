@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">سجل المراجعة</div>
                <div class="card-body">
                    <form method="GET" class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label for="from" class="form-label">من تاريخ</label>
                            <input type="date" id="from" name="from" class="form-control" value="{{ request('from') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="to" class="form-label">إلى تاريخ</label>
                            <input type="date" id="to" name="to" class="form-control" value="{{ request('to') }}">
                        </div>
                        <div class="col-md-3 align-self-end">
                            <button type="submit" class="btn btn-primary">تصفية</button>
                            <a href="{{ route('audit_logs.index') }}" class="btn btn-secondary">إعادة تعيين</a>
                        </div>
                    </form>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>الرقم</th>
                                <th>المستخدم</th>
                                <th>الإجراء</th>
                                <th>اسم الجدول</th>
                                <th>رقم السجل</th>
                                <th>القيم القديمة</th>
                                <th>القيم الجديدة</th>
                                <th>التوقيت</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>{{ $log->user ? $log->user->name : 'غير متوفر' }}</td>
                                <td>{{ $log->action }}</td>
                                <td>{{ $log->table_name }}</td>
                                <td>{{ $log->record_id }}</td>
                                <td>
                                    <span class="text-info">عرض التفاصيل</span>
                                </td>
                                <td>
                                    <span class="text-info">عرض التفاصيل</span>
                                </td>
                                <td>{{ $log->created_at }}</td>
                                <td>
                                    <a href="{{ route('audit_logs.show', $log->id) }}" class="btn btn-info btn-sm">عرض</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 