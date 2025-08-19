@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">أنواع المصروفات</h4>
    <a href="{{ route('expense_types.create') }}" class="btn btn-success mb-3">إضافة نوع جديد</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>الرقم</th>
                <th>الاسم</th>
                <th>الوصف</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($types as $type)
            <tr>
                <td>{{ $type->id }}</td>
                <td>{{ $type->name }}</td>
                <td>{{ $type->description }}</td>
                <td>
                    <a href="{{ route('expense_types.show', $type->id) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('expense_types.edit', $type->id) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <form action="{{ route('expense_types.destroy', $type->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $types->links() }}
</div>
@endsection 