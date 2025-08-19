@extends('layouts.app')

@section('content')
<div class="container">
    <h1>تعديل فئة المنتجات: {{ $itemCategory->name }}</h1>

    <form action="{{ route('item_categories.update', $itemCategory->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">اسم الفئة</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $itemCategory->name) }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">الوصف</label>
            <textarea class="form-control" id="description" name="description">{{ old('description', $itemCategory->description) }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">تحديث الفئة</button>
        <a href="{{ route('item_categories.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 