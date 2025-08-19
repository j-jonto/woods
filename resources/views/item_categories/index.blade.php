@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>فئات المنتجات</h1>
        <a href="{{ route('item_categories.create') }}" class="btn btn-primary">إضافة فئة جديدة</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>الاسم</th>
                <th>الوصف</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->description ?? 'غير محدد' }}</td>
                    <td>
                        <a href="{{ route('item_categories.edit', $category->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                        <form action="{{ route('item_categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفئة؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $categories->links() }}
</div>
@endsection 