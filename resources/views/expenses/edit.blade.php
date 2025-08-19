@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تعديل مصروف</h4>
    <form action="{{ route('expenses.update', $expense->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="type_id" class="form-label">نوع المصروف</label>
            <select name="type_id" id="type_id" class="form-select" required>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" {{ $expense->type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">المبلغ</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" value="{{ $expense->amount }}" required>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">التاريخ</label>
            <input type="date" name="date" id="date" class="form-control" value="{{ $expense->date }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">الوصف</label>
            <textarea name="description" id="description" class="form-control">{{ $expense->description }}</textarea>
        </div>
        <div class="mb-3">
            <label for="reference_no" class="form-label">المرجع</label>
            <input type="text" name="reference_no" id="reference_no" class="form-control" value="{{ $expense->reference_no }}">
        </div>
        <button type="submit" class="btn btn-primary">تحديث</button>
        <a href="{{ route('expenses.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection