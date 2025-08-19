@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تعديل إهلاك أصل</h4>
    <form action="{{ route('asset_depreciations.update', $assetDepreciation->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="asset_id" class="form-label">الأصل</label>
            <select name="asset_id" id="asset_id" class="form-select" required>
                @foreach($assets as $asset)
                    <option value="{{ $asset->id }}" {{ $assetDepreciation->asset_id == $asset->id ? 'selected' : '' }}>{{ $asset->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">التاريخ</label>
            <input type="date" name="date" id="date" class="form-control" value="{{ $assetDepreciation->date }}" required>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">المبلغ</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" value="{{ $assetDepreciation->amount }}" required>
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">ملاحظات</label>
            <textarea name="notes" id="notes" class="form-control">{{ $assetDepreciation->notes }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">تحديث</button>
        <a href="{{ route('asset_depreciations.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 