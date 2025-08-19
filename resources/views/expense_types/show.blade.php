@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تفاصيل نوع المصروف</h4>
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>اسم النوع:</strong> {{ $expenseType->name }}</p>
            <p><strong>الوصف:</strong> {{ $expenseType->description }}</p>
        </div>
    </div>
    <a href="{{ route('expense_types.index') }}" class="btn btn-secondary">رجوع</a>
</div>
@endsection 