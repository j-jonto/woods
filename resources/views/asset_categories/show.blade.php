@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تفاصيل تصنيف الأصل</h4>
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>اسم التصنيف:</strong> {{ $assetCategory->name }}</p>
            <p><strong>الوصف:</strong> {{ $assetCategory->description }}</p>
        </div>
    </div>
    <a href="{{ route('asset_categories.index') }}" class="btn btn-secondary">رجوع</a>
</div>
@endsection 