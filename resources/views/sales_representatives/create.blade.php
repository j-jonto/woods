@extends('layouts.app')

@section('content')
<div class="container">
    <h3>إضافة مندوب جديد</h3>
    <form method="POST" action="{{ route('sales_representatives.store') }}">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">اسم المندوب</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">رقم الهاتف</label>
            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">البريد الإلكتروني</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">العنوان</label>
            <textarea name="address" id="address" class="form-control">{{ old('address') }}</textarea>
        </div>
        <div class="mb-3">
            <label for="commission_rate" class="form-label">نسبة العمولة الإضافية (%)</label>
            <input type="number" step="0.01" name="commission_rate" id="commission_rate" class="form-control" value="{{ old('commission_rate', 0) }}">
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">نشط</label>
        </div>
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="{{ route('sales_representatives.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 