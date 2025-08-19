@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>مراكز العمل</h1>
        <a href="{{ route('work_centers.create') }}" class="btn btn-primary">إضافة مركز عمل جديد</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>الرمز</th>
                <th>الاسم</th>
                <th>الوصف</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($workCenters as $workCenter)
                <tr>
                    <td>{{ $workCenter->code }}</td>
                    <td>{{ $workCenter->name }}</td>
                    <td>{{ $workCenter->description ?? 'غير محدد' }}</td>
                    <td>
                        <span class="badge bg-{{ $workCenter->is_active ? 'success' : 'danger' }}">
                            {{ $workCenter->is_active ? 'نشط' : 'غير نشط' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('work_centers.edit', $workCenter->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                        <form action="{{ route('work_centers.destroy', $workCenter->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف مركز العمل هذا؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $workCenters->links() }}
</div>
@endsection 