@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تفاصيل المصروف</h4>
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>نوع المصروف:</strong> {{ $expense->type ? $expense->type->name : '-' }}</p>
            <p><strong>المبلغ:</strong> {{ number_format($expense->amount, 2) }} دينار ليبي</p>
            <p><strong>التاريخ:</strong> {{ $expense->date }}</p>
            <p><strong>الوصف:</strong> {{ $expense->description }}</p>
            <p><strong>المرجع:</strong> {{ $expense->reference_no }}</p>
        </div>
    </div>
    <a href="{{ route('expenses.index') }}" class="btn btn-secondary">رجوع</a>
    <a href="{{ route('print.expenses_report') }}" class="btn btn-success" target="_blank">
        <i class="fas fa-print"></i> طباعة تقرير المصروفات
    </a>
</div>
@endsection