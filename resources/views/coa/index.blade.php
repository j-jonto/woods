@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>دليل الحسابات</h1>
        <a href="{{ route('coa.create') }}" class="btn btn-primary">إضافة حساب جديد</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>الرمز</th>
                <th>الاسم</th>
                <th>النوع</th>
                <th>المستوى</th>
                <th>الحساب الرئيسي</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($accounts as $account)
                @include('coa.partials.account_row', ['account' => $account, 'level' => 0])
            @endforeach
        </tbody>
    </table>
</div>

@endsection 