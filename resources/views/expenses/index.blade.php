@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">المصروفات</h4>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('expenses.create') }}" class="btn btn-success">إضافة مصروف جديد</a>
        <a href="{{ route('print.expenses_report') }}" class="btn btn-info" target="_blank">
            <i class="fas fa-print"></i> طباعة تقرير المصروفات
        </a>
    </div>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>الرقم</th>
                <th>النوع</th>
                <th>المبلغ</th>
                <th>التاريخ</th>
                <th>الوصف</th>
                <th>المرجع</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->expense_no }}</td>
                <td>{{ $expense->expenseType ? $expense->expenseType->name : '-' }}</td>
                <td>{{ number_format($expense->amount, 2) }} ريال</td>
                <td>{{ $expense->expense_date }}</td>
                <td>{{ $expense->description }}</td>
                <td>{{ $expense->reference_no }}</td>
                <td>
                    <a href="{{ route('expenses.show', $expense->id) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $expenses->links() }}
</div>
@endsection