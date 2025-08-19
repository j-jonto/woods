@extends('layouts.app')

@section('content')
<div class="container">
    <h1>إضافة منتج جديد</h1>

    <form action="{{ route('items.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="code" class="form-label">رمز المنتج</label>
            <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">اسم المنتج</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">الوصف</label>
            <textarea class="form-control" id="description" name="description">{{ old('description') }}</textarea>
        </div>
        <div class="mb-3">
            <label for="type" class="form-label">نوع المنتج</label>
            <select class="form-select" id="type" name="type" required>
                <option value="">اختر النوع</option>
                <option value="raw_material" {{ old('type') == 'raw_material' ? 'selected' : '' }}>مواد خام</option>
                <option value="wip" {{ old('type') == 'wip' ? 'selected' : '' }}>قيد التصنيع</option>
                <option value="finished_goods" {{ old('type') == 'finished_goods' ? 'selected' : '' }}>منتجات نهائية</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="category_id" class="form-label">الفئة</label>
            <select class="form-select" id="category_id" name="category_id">
                <option value="">اختر الفئة</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="unit_of_measure" class="form-label">وحدة القياس</label>
            <input type="text" class="form-control" id="unit_of_measure" name="unit_of_measure" value="{{ old('unit_of_measure') }}" required>
        </div>
        <div class="mb-3">
            <label for="standard_cost" class="form-label">التكلفة القياسية</label>
            <input type="number" step="0.01" class="form-control" id="standard_cost" name="standard_cost" value="{{ old('standard_cost', 0) }}">
        </div>
        <div class="mb-3">
            <label for="selling_price" class="form-label">سعر البيع</label>
            <input type="number" step="0.01" class="form-control" id="selling_price" name="selling_price" value="{{ old('selling_price', 0) }}">
        </div>
        <div class="mb-3">
            <label for="reorder_point" class="form-label">نقطة إعادة الطلب</label>
            <input type="number" step="0.01" class="form-control" id="reorder_point" name="reorder_point" value="{{ old('reorder_point', 0) }}">
        </div>
        <div class="mb-3">
            <label for="reorder_quantity" class="form-label">كمية إعادة الطلب</label>
            <input type="number" step="0.01" class="form-control" id="reorder_quantity" name="reorder_quantity" value="{{ old('reorder_quantity', 0) }}">
        </div>
        <div class="mb-3">
            <label for="barcode" class="form-label">الباركود</label>
            <input type="text" name="barcode" id="barcode" class="form-control" value="{{ old('barcode') }}">
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">نشط</label>
        </div>
        <button type="submit" class="btn btn-primary">إنشاء المنتج</button>
        <a href="{{ route('items.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 