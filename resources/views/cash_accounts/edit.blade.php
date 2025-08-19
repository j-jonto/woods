@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تعديل حساب</h4>
    <form action="{{ route('cash_accounts.update', $cashAccount->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">اسم الحساب</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $cashAccount->name }}" required>
        </div>
        <div class="mb-3">
            <label for="type" class="form-label">نوع الحساب</label>
            <select name="type" id="type" class="form-select" required>
                <option value="cash" {{ $cashAccount->type == 'cash' ? 'selected' : '' }}>صندوق</option>
                <option value="bank" {{ $cashAccount->type == 'bank' ? 'selected' : '' }}>بنك</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="balance" class="form-label">الرصيد</label>
            <input type="number" step="0.01" name="balance" id="balance" class="form-control" value="{{ $cashAccount->balance }}">
        </div>
        <div class="mb-3">
            <label for="is_active" class="form-label">الحالة</label>
            <select name="is_active" id="is_active" class="form-select">
                <option value="1" {{ $cashAccount->is_active ? 'selected' : '' }}>نشط</option>
                <option value="0" {{ !$cashAccount->is_active ? 'selected' : '' }}>غير نشط</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">تحديث</button>
        <a href="{{ route('cash_accounts.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 