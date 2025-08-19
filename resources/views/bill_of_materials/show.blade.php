@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <h1>تفاصيل قائمة المواد (BOM)</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('boms.edit', $billOfMaterial->id) }}" class="btn btn-primary">تعديل</a>
            <a href="{{ route('boms.index') }}" class="btn btn-secondary">العودة للقائمة</a>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">معلومات قائمة المواد</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>المنتج النهائي:</h6>
                    <p class="text-primary">{{ $billOfMaterial->finishedGood->name ?? 'غير محدد' }} ({{ $billOfMaterial->finishedGood->code ?? 'غير محدد' }})</p>
                </div>
                <div class="col-md-6">
                    <h6>المواد الخام:</h6>
                    <p class="text-success">{{ $billOfMaterial->rawMaterial->name ?? 'غير محدد' }} ({{ $billOfMaterial->rawMaterial->code ?? 'غير محدد' }})</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h6>الكمية المطلوبة:</h6>
                    <p>{{ $billOfMaterial->quantity }} {{ $billOfMaterial->rawMaterial->unit_of_measure ?? 'وحدة' }}</p>
                </div>
                <div class="col-md-6">
                    <h6>الحالة:</h6>
                    <span class="badge {{ $billOfMaterial->is_active ? 'bg-success' : 'bg-danger' }}">
                        {{ $billOfMaterial->is_active ? 'نشطة' : 'غير نشطة' }}
                    </span>
                </div>
            </div>

            @if($billOfMaterial->description)
            <div class="row">
                <div class="col-12">
                    <h6>الوصف:</h6>
                    <p>{{ $billOfMaterial->description }}</p>
                </div>
            </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <h6>تاريخ الإنشاء:</h6>
                    <p>{{ $billOfMaterial->created_at->format('Y-m-d H:i') }}</p>
                </div>
                <div class="col-md-6">
                    <h6>آخر تحديث:</h6>
                    <p>{{ $billOfMaterial->updated_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($billOfMaterial->finishedGood && $billOfMaterial->rawMaterial)
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">معلومات إضافية</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>تكلفة المواد الخام:</h6>
                    <p>{{ $billOfMaterial->rawMaterial->standard_cost ?? 0 }} دينار ليبي</p>
                </div>
                <div class="col-md-6">
                    <h6>التكلفة الإجمالية للمواد:</h6>
                    <p class="text-danger">{{ ($billOfMaterial->rawMaterial->standard_cost ?? 0) * $billOfMaterial->quantity }} دينار ليبي</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection 