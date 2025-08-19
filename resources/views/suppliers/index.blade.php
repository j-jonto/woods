@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>الموردين</h1>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">إضافة مورد جديد</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>الاسم</th>
                <th>البريد الإلكتروني</th>
                <th>الهاتف</th>
                <th>العنوان</th>
                <th>الشخص المسؤول</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($suppliers as $supplier)
                <tr>
                    <td>{{ $supplier->name }}</td>
                    <td>{{ $supplier->email ?? 'غير متوفر' }}</td>
                    <td>{{ $supplier->phone ?? 'غير متوفر' }}</td>
                    <td>{{ Str::limit($supplier->address, 50) ?? 'غير متوفر' }}</td>
                    <td>{{ $supplier->contact_person ?? 'غير متوفر' }}</td>
                    <td>
                        <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-sm btn-info">عرض</a>
                        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                        <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المورد؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $suppliers->links() }}
</div>
@endsection 