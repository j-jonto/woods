@extends('layouts.app')

@section('content')
<div class="container">
    <h1>إضافة أصل ثابت جديد</h1>
    <form action="{{ route('fixed_assets.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="code" class="form-label">رمز الأصل</label>
            <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">اسم الأصل</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="mb-3">
            <label for="acquisition_date" class="form-label">تاريخ الاكتساب</label>
            <input type="date" class="form-control" id="acquisition_date" name="acquisition_date" value="{{ old('acquisition_date') }}" required>
        </div>
        <div class="mb-3">
            <label for="acquisition_cost" class="form-label">تكلفة الاكتساب</label>
            <input type="number" step="0.01" class="form-control" id="acquisition_cost" name="acquisition_cost" value="{{ old('acquisition_cost') }}" required>
        </div>
        <div class="mb-3">
            <label for="useful_life" class="form-label">العمر الإنتاجي (بالسنوات)</label>
            <input type="number" class="form-control" id="useful_life" name="useful_life" value="{{ old('useful_life') }}" required>
        </div>
        <div class="mb-3">
            <label for="depreciation_method" class="form-label">طريقة الإهلاك</label>
            <select class="form-select" id="depreciation_method" name="depreciation_method" required>
                <option value="">اختر الطريقة</option>
                <option value="straight_line" {{ old('depreciation_method') == 'straight_line' ? 'selected' : '' }}>القسط الثابت</option>
                <option value="declining_balance" {{ old('depreciation_method') == 'declining_balance' ? 'selected' : '' }}>القسط المتناقص</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">الوصف (اختياري)</label>
            <textarea class="form-control" id="description" name="description">{{ old('description') }}</textarea>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">الحالة</label>
            <select class="form-select" id="status" name="status">
                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>نشط</option>
                <option value="disposed" {{ old('status') == 'disposed' ? 'selected' : '' }}>متصرف به</option>
                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>قيد الصيانة</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">إضافة الأصل</button>
        <a href="{{ route('fixed_assets.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 