@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تصنيفات الأصول</h4>
    <a href="{{ route('asset_categories.create') }}" class="btn btn-success mb-3">إضافة تصنيف جديد</a>
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
            @foreach($categories as $category)
            <tr>
                <td>{{ $category->id }}</td>
                <td>{{ $category->name }}</td>
                <td>{{ $category->description }}</td>
                <td>
                    <a href="{{ route('asset_categories.show', $category->id) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('asset_categories.edit', $category->id) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <form action="{{ route('asset_categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $categories->links() }}
</div>
@endsection 