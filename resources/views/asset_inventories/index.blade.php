@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">جرد الأصول</h4>
    <a href="{{ route('asset_inventories.create') }}" class="btn btn-success mb-3">إضافة جرد جديد</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>الرقم</th>
                <th>الأصل</th>
                <th>الموقع</th>
                <th>الحالة</th>
                <th>تاريخ الجرد</th>
                <th>ملاحظات</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventories as $inventory)
            <tr>
                <td>{{ $inventory->id }}</td>
                <td>{{ $inventory->asset ? $inventory->asset->name : '-' }}</td>
                <td>{{ $inventory->location }}</td>
                <td>{{ $inventory->status }}</td>
                <td>{{ $inventory->inventory_date }}</td>
                <td>{{ $inventory->notes }}</td>
                <td>
                    <a href="{{ route('asset_inventories.show', $inventory->id) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('asset_inventories.edit', $inventory->id) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <form action="{{ route('asset_inventories.destroy', $inventory->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $inventories->links() }}
</div>
@endsection 