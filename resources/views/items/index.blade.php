@extends('layouts.app')

@section('content')
<div class="container">
    <!-- رسائل التنبيه -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>المنتجات</h1>
        <a href="{{ route('items.create') }}" class="btn btn-primary">إضافة منتج جديد</a>
    </div>

    <div class="mb-3">
        <input type="text" id="item-search" class="form-control" placeholder="ابحث عن منتج بالاسم أو الرمز أو النوع أو الباركود...">
    </div>
    <table class="table table-striped table-bordered" id="items-table">
        <thead>
            <tr>
                <th>الرمز</th>
                <th>الاسم</th>
                <th>النوع</th>
                <th>الفئة</th>
                <th>وحدة القياس</th>
                <th class="text-end">المخزون المتاح</th>
                <th class="text-end">التكلفة القياسية</th>
                <th class="text-end">سعر البيع</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item->code }}</td>
                    <td>{{ $item->name }}</td>
                    <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $item->type)) }}</span></td>
                    <td>{{ $item->category->name ?? 'غير محدد' }}</td>
                    <td>{{ $item->unit_of_measure }}</td>
                    <td class="text-end">
                        @if($item->available_stock > 0)
                            <span class="badge bg-success">{{ number_format($item->available_stock, 2) }}</span>
                        @elseif($item->available_stock == 0)
                            <span class="badge bg-danger">نفذ المخزون</span>
                        @else
                            <span class="badge bg-warning">{{ number_format($item->available_stock, 2) }}</span>
                        @endif
                    </td>
                    <td class="text-end">${{ number_format($item->standard_cost, 2) }}</td>
                    <td class="text-end">${{ number_format($item->selling_price, 2) }}</td>
                    <td>
                        <span class="badge bg-{{ $item->is_active ? 'success' : 'danger' }}">
                            {{ $item->is_active ? 'نشط' : 'غير نشط' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('items.show', $item->id) }}" class="btn btn-sm btn-info">عرض</a>
                        <a href="{{ route('items.edit', $item->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                        <form action="{{ route('items.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المنتج؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $items->links() }}
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('item-search');
    const table = document.getElementById('items-table');
    searchInput.addEventListener('input', function() {
        const value = this.value.trim().toLowerCase();
        for (const row of table.tBodies[0].rows) {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(value) ? '' : 'none';
        }
    });
});
</script> 