@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">تعديل إيراد</h4>
    <form action="{{ route('revenues.update', $revenue->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="type_id" class="form-label">نوع الإيراد</label>
            <select name="type_id" id="type_id" class="form-select" required>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" {{ $revenue->type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">المبلغ</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control" value="{{ $revenue->amount }}" required>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">التاريخ</label>
            <input type="date" name="date" id="date" class="form-control" value="{{ $revenue->date }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">الوصف</label>
            <textarea name="description" id="description" class="form-control">{{ $revenue->description }}</textarea>
        </div>
        <div class="mb-3">
            <label for="reference_no" class="form-label">المرجع</label>
            <input type="text" name="reference_no" id="reference_no" class="form-control" value="{{ $revenue->reference_no }}">
        </div>
        <button type="submit" class="btn btn-primary">تحديث</button>
        <a href="{{ route('revenues.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection 