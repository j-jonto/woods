@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">إهلاك الأصول</h4>
    <a href="{{ route('asset_depreciations.create') }}" class="btn btn-success mb-3">إضافة إهلاك جديد</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>الرقم</th>
                <th>الأصل</th>
                <th>التاريخ</th>
                <th>المبلغ</th>
                <th>ملاحظات</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($depreciations as $depreciation)
            <tr>
                <td>{{ $depreciation->id }}</td>
                <td>{{ $depreciation->asset ? $depreciation->asset->name : '-' }}</td>
                <td>{{ $depreciation->date }}</td>
                <td>{{ number_format($depreciation->amount, 2) }}</td>
                <td>{{ $depreciation->notes }}</td>
                <td>
                    <a href="{{ route('asset_depreciations.show', $depreciation->id) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('asset_depreciations.edit', $depreciation->id) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <form action="{{ route('asset_depreciations.destroy', $depreciation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $depreciations->links() }}
</div>
@endsection 