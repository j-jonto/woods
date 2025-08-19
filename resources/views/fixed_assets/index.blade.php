@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>الأصول الثابتة</h1>
        <a href="{{ route('fixed_assets.create') }}" class="btn btn-primary">إضافة أصل ثابت جديد</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>رقم الأصل</th>
                <th>الاسم</th>
                <th>تاريخ الاكتساب</th>
                <th class="text-end">تكلفة الاكتساب</th>
                <th class="text-end">القيمة الحالية</th>
                <th>طريقة الإهلاك</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($assets as $asset)
                <tr>
                    <td>{{ $asset->asset_tag }}</td>
                    <td>{{ $asset->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($asset->acquisition_date)->format('Y-m-d') }}</td>
                    <td class="text-end">${{ number_format($asset->acquisition_cost, 2) }}</td>
                    <td class="text-end">${{ number_format($asset->getCurrentBookValue(), 2) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $asset->depreciation_method)) }}</td>
                    <td>
                        <span class="badge bg-{{ $asset->status == 'active' ? 'success' : ($asset->status == 'disposed' ? 'danger' : 'warning') }}">
                            {{ $asset->status == 'active' ? 'نشط' : ($asset->status == 'disposed' ? 'متصرف به' : 'قيد الصيانة') }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('fixed_assets.show', $asset->id) }}" class="btn btn-sm btn-info">عرض</a>
                        @if ($asset->status == 'active')
                            <a href="{{ route('fixed_assets.edit', $asset->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                            <form action="{{ route('fixed_assets.dispose', $asset->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من التصرف بهذا الأصل؟');">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-sm btn-danger">تصرف</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $assets->links() }}
</div>
@endsection 