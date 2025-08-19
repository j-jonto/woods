@extends('layouts.app')

@section('content')
<div class="container">
    <h1>تعديل المورد: {{ $supplier->name }}</h1>

    <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">اسم المورد</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $supplier->name) }}" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">البريد الإلكتروني (اختياري)</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $supplier->email) }}">
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">رقم الهاتف (اختياري)</label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $supplier->phone) }}">
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">العنوان (اختياري)</label>
            <textarea class="form-control" id="address" name="address">{{ old('address', $supplier->address) }}</textarea>
        </div>
        <div class="mb-3">
            <label for="contact_person" class="form-label">الشخص المسؤول (اختياري)</label>
            <input type="text" class="form-control" id="contact_person" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}">
        </div>
        <div class="mb-3">
            <label for="code" class="form-label">رمز المورد</label>
            <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $supplier->code) }}" readonly>
        </div>
        <button type="submit" class="btn btn-primary">تحديث المورد</button>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 