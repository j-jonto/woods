@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تفاصيل إهلاك الأصل</h4>
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>الأصل:</strong> {{ $assetDepreciation->asset ? $assetDepreciation->asset->name : '-' }}</p>
            <p><strong>التاريخ:</strong> {{ $assetDepreciation->date }}</p>
            <p><strong>المبلغ:</strong> {{ number_format($assetDepreciation->amount, 2) }}</p>
            <p><strong>ملاحظات:</strong> {{ $assetDepreciation->notes }}</p>
        </div>
    </div>
    <a href="{{ route('asset_depreciations.index') }}" class="btn btn-secondary">رجوع</a>
</div>
@endsection 