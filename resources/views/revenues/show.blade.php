@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تفاصيل الإيراد</h4>
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>نوع الإيراد:</strong> {{ $revenue->type ? $revenue->type->name : '-' }}</p>
            <p><strong>المبلغ:</strong> {{ number_format($revenue->amount, 2) }} دينار ليبي</p>
            <p><strong>التاريخ:</strong> {{ $revenue->date }}</p>
            <p><strong>الوصف:</strong> {{ $revenue->description }}</p>
            <p><strong>المرجع:</strong> {{ $revenue->reference_no }}</p>
        </div>
    </div>
    <a href="{{ route('revenues.index') }}" class="btn btn-secondary">رجوع</a>
    <a href="{{ route('print.revenues_report') }}" class="btn btn-success" target="_blank">
        <i class="fas fa-print"></i> طباعة تقرير الإيرادات
    </a>
</div>
@endsection 