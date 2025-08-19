@extends('layouts.app')

@section('content')
<div class="container">
    <h1>إضافة مورد جديد</h1>

    <form action="{{ route('suppliers.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="code" class="form-label">رمز المورد</label>
            <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">اسم المورد</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">البريد الإلكتروني (اختياري)</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">رقم الهاتف (اختياري)</label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">العنوان (اختياري)</label>
            <textarea class="form-control" id="address" name="address">{{ old('address') }}</textarea>
        </div>
        <div class="mb-3">
            <label for="contact_person" class="form-label">الشخص المسؤول (اختياري)</label>
            <input type="text" class="form-control" id="contact_person" name="contact_person" value="{{ old('contact_person') }}">
        </div>
        <button type="submit" class="btn btn-primary">إنشاء المورد</button>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 