@extends('layouts.app')

@section('content')
<div class="container">
    <h1>إنشاء مركز عمل جديد</h1>

    <form action="{{ route('work_centers.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="code" class="form-label">رمز مركز العمل</label>
            <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">اسم مركز العمل</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">الوصف</label>
            <textarea class="form-control" id="description" name="description">{{ old('description') }}</textarea>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">نشط</label>
        </div>
        <button type="submit" class="btn btn-primary">إنشاء مركز العمل</button>
        <a href="{{ route('work_centers.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 