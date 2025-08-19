@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تعديل نوع مصروف</h4>
    <form action="{{ route('expense_types.update', $expenseType->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">اسم النوع</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $expenseType->name }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">الوصف</label>
            <textarea name="description" id="description" class="form-control">{{ $expenseType->description }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">تحديث</button>
        <a href="{{ route('expense_types.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 