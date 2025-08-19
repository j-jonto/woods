@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">إضافة نوع مصروف جديد</h4>
    <form action="{{ route('expense_types.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">اسم النوع</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">الوصف</label>
            <textarea name="description" id="description" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="{{ route('expense_types.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 