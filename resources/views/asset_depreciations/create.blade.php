@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">إضافة إهلاك أصل جديد</h4>
    <form action="{{ route('asset_depreciations.store') }}" method="POST">
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
            <label for="date" class="form-label">التاريخ</label>
            <input type="date" name="date" id="date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">المبلغ</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">ملاحظات</label>
            <textarea name="notes" id="notes" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="{{ route('asset_depreciations.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 