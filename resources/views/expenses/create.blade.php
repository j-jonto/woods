@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">إضافة مصروف جديد</h4>
    <form action="{{ route('expenses.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="type_id" class="form-label">نوع المصروف</label>
            <select name="type_id" id="type_id" class="form-select" required>
                <option value="">اختر النوع</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">المبلغ</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">التاريخ</label>
            <input type="date" name="date" id="date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">الوصف</label>
            <textarea name="description" id="description" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label for="reference_no" class="form-label">المرجع</label>
            <input type="text" name="reference_no" id="reference_no" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="{{ route('expenses.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection