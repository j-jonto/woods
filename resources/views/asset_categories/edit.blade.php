@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تعديل تصنيف أصل</h4>
    <form action="{{ route('asset_categories.update', $assetCategory->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">اسم التصنيف</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $assetCategory->name }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">الوصف</label>
            <textarea name="description" id="description" class="form-control">{{ $assetCategory->description }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">تحديث</button>
        <a href="{{ route('asset_categories.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 