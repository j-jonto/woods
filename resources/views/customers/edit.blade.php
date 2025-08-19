@extends('layouts.app')

@section('content')
<div class="container">
    <h1>تعديل العميل: {{ $customer->name }}</h1>

    <form action="{{ route('customers.update', $customer->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">اسم العميل</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $customer->name) }}" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">البريد الإلكتروني (اختياري)</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $customer->email) }}">
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">رقم الهاتف (اختياري)</label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $customer->phone) }}">
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">العنوان (اختياري)</label>
            <textarea class="form-control" id="address" name="address">{{ old('address', $customer->address) }}</textarea>
        </div>
        <div class="mb-3">
            <label for="credit_limit" class="form-label">الحد الائتماني (اختياري)</label>
            <input type="number" step="0.01" min="0" class="form-control" id="credit_limit" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}">
            <div class="form-text">اترك القيمة 0 لحد ائتماني غير محدود.</div>
        </div>
        <div class="mb-3">
            <label for="contact_person" class="form-label">الشخص المسؤول (اختياري)</label>
            <input type="text" class="form-control" id="contact_person" name="contact_person" value="{{ old('contact_person', $customer->contact_person) }}">
        </div>
        <div class="mb-3">
            <label for="code" class="form-label">رمز العميل</label>
            <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $customer->code) }}" readonly>
        </div>
        <button type="submit" class="btn btn-primary">تحديث العميل</button>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 