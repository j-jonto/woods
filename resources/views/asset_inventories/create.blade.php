@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">إضافة جرد أصل جديد</h4>
    <form action="{{ route('asset_inventories.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="asset_id" class="form-label">الأصل</label>
            <select name="asset_id" id="asset_id" class="form-select" required>
                <option value="">اختر الأصل</option>
                @foreach($assets as $asset)
                    <option value="{{ $asset->id }}">{{ $asset->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="location" class="form-label">الموقع</label>
            <input type="text" name="location" id="location" class="form-control">
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">الحالة</label>
            <input type="text" name="status" id="status" class="form-control">
        </div>
        <div class="mb-3">
            <label for="inventory_date" class="form-label">تاريخ الجرد</label>
            <input type="date" name="inventory_date" id="inventory_date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">ملاحظات</label>
            <textarea name="notes" id="notes" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="{{ route('asset_inventories.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 