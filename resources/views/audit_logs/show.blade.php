@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Audit Log Details - {{ $auditLog->id }}</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="user">User:</label>
                        <p>{{ $auditLog->user ? $auditLog->user->name : 'N/A' }}</p>
                    </div>
                    <div class="form-group">
                        <label for="action">Action:</label>
                        <p>{{ $auditLog->action }}</p>
                    </div>
                    <div class="form-group">
                        <label for="table_name">Table Name:</label>
                        <p>{{ $auditLog->table_name }}</p>
                    </div>
                    <div class="form-group">
                        <label for="record_id">Record ID:</label>
                        <p>{{ $auditLog->record_id }}</p>
                    </div>
                    <div class="form-group">
                        <label for="old_values">القيم القديمة:</label>
                        @php $old = json_decode($auditLog->old_values, true); @endphp
                        @if($old)
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>الحقل</th><th>القيمة</th></tr></thead>
                            <tbody>
                            @foreach($old as $key => $value)
                                <tr>
                                    <td>{{ $key }}</td>
                                    <td>{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @else
                        <span class="text-muted">لا يوجد</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="new_values">القيم الجديدة:</label>
                        @php $new = json_decode($auditLog->new_values, true); @endphp
                        @if($new)
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>الحقل</th><th>القيمة</th></tr></thead>
                            <tbody>
                            @foreach($new as $key => $value)
                                <tr>
                                    <td>{{ $key }}</td>
                                    <td>{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @else
                        <span class="text-muted">لا يوجد</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="created_at">Timestamp:</label>
                        <p>{{ $auditLog->created_at }}</p>
                    </div>
                    <a href="{{ route('audit_logs.index') }}" class="btn btn-primary">Back to Audit Logs</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 