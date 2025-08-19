@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h4 class="mb-0">تعديل الخزنة العامة</h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('treasury.update', $treasury->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">اسم الخزنة</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $treasury->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="opening_balance" class="form-label">الرصيد الافتتاحي</label>
                            <input type="number" step="0.01" class="form-control" id="opening_balance" name="opening_balance" value="{{ old('opening_balance', $treasury->opening_balance) }}" required>
                            <div class="form-text">هذا الرصيد سيتم إضافته للرصيد الحالي عند الحفظ</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">الوصف</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $treasury->description) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $treasury->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    الخزنة نشطة
                                </label>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h6>معلومات الخزنة الحالية:</h6>
                            <ul class="mb-0">
                                <li><strong>الرصيد الحالي:</strong> {{ number_format($treasury->current_balance, 2) }} دينار ليبي</li>
                                <li><strong>إجمالي القبض:</strong> {{ number_format($treasury->total_receipts, 2) }} دينار ليبي</li>
                                <li><strong>إجمالي الصرف:</strong> {{ number_format($treasury->total_payments, 2) }} دينار ليبي</li>
                                <li><strong>تاريخ الإنشاء:</strong> {{ $treasury->created_at->format('Y-m-d H:i') }}</li>
                                <li><strong>آخر تحديث:</strong> {{ $treasury->updated_at->format('Y-m-d H:i') }}</li>
                            </ul>
                        </div>

                                                            <div class="d-flex justify-content-between">
                                        <a href="{{ route('treasury.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-right"></i> رجوع
                                        </a>
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-save"></i> تحديث الخزنة
                                        </button>
                                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 