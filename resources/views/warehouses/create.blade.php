@extends('layouts.app')

@section('content')
<div class="container">
    <h1>إنشاء مستودع جديد</h1>

    <form action="{{ route('warehouses.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="code" class="form-label">رمز المستودع</label>
            <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">اسم المستودع</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="mb-3">
            <label for="location" class="form-label">الموقع</label>
            <textarea class="form-control" id="location" name="location">{{ old('location') }}</textarea>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">نشط</label>
        </div>
        <button type="submit" class="btn btn-primary">إنشاء المستودع</button>
        <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 