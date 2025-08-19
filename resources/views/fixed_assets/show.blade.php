@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">تفاصيل الأصل الثابت: {{ $fixedAsset->name }}</h1>
    <div class="card">
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-4 font-weight-bold">رمز الأصل:</div>
                <div class="col-md-8">{{ $fixedAsset->code }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4 font-weight-bold">اسم الأصل:</div>
                <div class="col-md-8">{{ $fixedAsset->name }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4 font-weight-bold">تاريخ الاكتساب:</div>
                <div class="col-md-8">{{ $fixedAsset->acquisition_date }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4 font-weight-bold">تكلفة الاكتساب:</div>
                <div class="col-md-8">{{ $fixedAsset->acquisition_cost }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4 font-weight-bold">العمر الإنتاجي (بالسنوات):</div>
                <div class="col-md-8">{{ $fixedAsset->useful_life }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4 font-weight-bold">طريقة الإهلاك:</div>
                <div class="col-md-8">{{ $fixedAsset->depreciation_method }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4 font-weight-bold">الوصف:</div>
                <div class="col-md-8">{{ $fixedAsset->description }}</div>
            </div>
            <div class="row mb-2">
                <div class="col-md-4 font-weight-bold">الحالة:</div>
                <div class="col-md-8">
                    @if($fixedAsset->status == 'active')
                        <span class="badge bg-success">نشط</span>
                    @elseif($fixedAsset->status == 'disposed')
                        <span class="badge bg-danger">متصرف به</span>
                    @elseif($fixedAsset->status == 'maintenance')
                        <span class="badge bg-warning">قيد الصيانة</span>
                    @else
                        <span class="badge bg-secondary">غير معروف</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-between">
        <a href="{{ route('fixed_assets.index') }}" class="btn btn-secondary">العودة للقائمة</a>
        @if(($fixedAsset->depreciations ?? collect())->count() == 0 && ($fixedAsset->transactions ?? collect())->count() == 0)
            <form action="{{ route('fixed_assets.destroy', $fixedAsset->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف الأصل؟');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">حذف الأصل</button>
            </form>
        @endif
    </div>
</div>
@endsection 