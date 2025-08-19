@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>المستودعات</h1>
        <a href="{{ route('warehouses.create') }}" class="btn btn-primary">إضافة مستودع جديد</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>الرمز</th>
                <th>الاسم</th>
                <th>الموقع</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($warehouses as $warehouse)
                <tr>
                    <td>{{ $warehouse->code }}</td>
                    <td>{{ $warehouse->name }}</td>
                    <td>{{ $warehouse->location ?? 'غير محدد' }}</td>
                    <td>
                        <span class="badge bg-{{ $warehouse->is_active ? 'success' : 'danger' }}">
                            {{ $warehouse->is_active ? 'نشط' : 'غير نشط' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('warehouses.edit', $warehouse->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                        <form action="{{ route('warehouses.destroy', $warehouse->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستودع؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $warehouses->links() }}
</div>
@endsection 