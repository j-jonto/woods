@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>العملاء</h1>
        <a href="{{ route('customers.create') }}" class="btn btn-primary">إضافة عميل جديد</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>الاسم</th>
                <th>البريد الإلكتروني</th>
                <th>رقم الهاتف</th>
                <th>العنوان</th>
                <th>الشخص المسؤول</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $customer)
                <tr>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->email ?? 'غير محدد' }}</td>
                    <td>{{ $customer->phone ?? 'غير محدد' }}</td>
                    <td>{{ Str::limit($customer->address, 50) ?? 'غير محدد' }}</td>
                    <td>{{ $customer->contact_person ?? 'غير محدد' }}</td>
                    <td>
                        <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-info">عرض</a>
                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                        <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا العميل؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $customers->links() }}
</div>
@endsection 