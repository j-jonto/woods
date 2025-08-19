@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">الإيرادات</h4>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('revenues.create') }}" class="btn btn-success">إضافة إيراد جديد</a>
        <a href="{{ route('print.revenues_report') }}" class="btn btn-info" target="_blank">
            <i class="fas fa-print"></i> طباعة تقرير الإيرادات
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
            @foreach($revenues as $revenue)
            <tr>
                <td>{{ $revenue->revenue_no }}</td>
                <td>{{ $revenue->revenueType ? $revenue->revenueType->name : '-' }}</td>
                <td>{{ number_format($revenue->amount, 2) }} ريال</td>
                <td>{{ $revenue->revenue_date }}</td>
                <td>{{ $revenue->description }}</td>
                <td>{{ $revenue->reference_no }}</td>
                <td>
                    <a href="{{ route('revenues.show', $revenue->id) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('revenues.edit', $revenue->id) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <form action="{{ route('revenues.destroy', $revenue->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $revenues->links() }}
</div>
@endsection 