@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>مندوبي المبيعات</h1>
        <a href="{{ route('sales_representatives.create') }}" class="btn btn-primary">إضافة مندوب جديد</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>الاسم</th>
                <th>الهاتف</th>
                <th>البريد الإلكتروني</th>
                <th>نسبة العمولة</th>
                <th>إجمالي المبيعات</th>
                <th>الرصيد الحالي</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($representatives as $rep)
                <tr>
                    <td>{{ $rep->name }}</td>
                    <td>{{ $rep->phone ?? 'غير محدد' }}</td>
                    <td>{{ $rep->email ?? 'غير محدد' }}</td>
                    <td>{{ $rep->commission_rate }}%</td>
                    <td>{{ number_format($rep->total_sales, 2) }} دينار ليبي</td>
                    <td>{{ number_format($rep->balance, 2) }} دينار ليبي</td>
                    <td>
                        <span class="badge bg-{{ $rep->is_active ? 'success' : 'danger' }}">
                            {{ $rep->is_active ? 'نشط' : 'غير نشط' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('sales_representatives.show', $rep->id) }}" class="btn btn-sm btn-info">عرض</a>
                        <a href="{{ route('sales_representatives.edit', $rep->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                        <form action="{{ route('sales_representatives.destroy', $rep->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المندوب؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $representatives->links() }}
</div>
@endsection 