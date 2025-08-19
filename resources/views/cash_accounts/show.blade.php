@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تفاصيل الحساب</h4>
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>اسم الحساب:</strong> {{ $cashAccount->name }}</p>
            <p><strong>نوع الحساب:</strong> {{ $cashAccount->type == 'cash' ? 'صندوق' : 'بنك' }}</p>
            <p><strong>الرصيد:</strong> {{ number_format($cashAccount->balance, 2) }} دينار ليبي</p>
            <p><strong>الحالة:</strong> {{ $cashAccount->is_active ? 'نشط' : 'غير نشط' }}</p>
        </div>
    </div>
    <a href="{{ route('cash_accounts.index') }}" class="btn btn-secondary">رجوع</a>
</div>
@endsection 