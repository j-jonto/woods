@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تفاصيل جرد الأصل</h4>
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>الأصل:</strong> {{ $assetInventory->asset ? $assetInventory->asset->name : '-' }}</p>
            <p><strong>الموقع:</strong> {{ $assetInventory->location }}</p>
            <p><strong>الحالة:</strong> {{ $assetInventory->status }}</p>
            <p><strong>تاريخ الجرد:</strong> {{ $assetInventory->inventory_date }}</p>
            <p><strong>ملاحظات:</strong> {{ $assetInventory->notes }}</p>
        </div>
    </div>
    <a href="{{ route('asset_inventories.index') }}" class="btn btn-secondary">رجوع</a>
</div>
@endsection 