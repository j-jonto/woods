@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Account: {{ $coa->name }}</h1>

    <form action="{{ route('coa.update', $coa->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="code" class="form-label">Account Code</label>
            <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $coa->code) }}" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Account Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $coa->name) }}" required>
        </div>
        <div class="mb-3">
            <label for="type" class="form-label">Account Type</label>
            <select class="form-select" id="type" name="type" required>
                <option value="">Select Type</option>
                <option value="asset" {{ old('type', $coa->type) == 'asset' ? 'selected' : '' }}>Asset</option>
                <option value="liability" {{ old('type', $coa->type) == 'liability' ? 'selected' : '' }}>Liability</option>
                <option value="equity" {{ old('type', $coa->type) == 'equity' ? 'selected' : '' }}>Equity</option>
                <option value="revenue" {{ old('type', $coa->type) == 'revenue' ? 'selected' : '' }}>Revenue</option>
                <option value="expense" {{ old('type', $coa->type) == 'expense' ? 'selected' : '' }}>Expense</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="parent_id" class="form-label">Parent Account (Optional)</label>
            <select class="form-select" id="parent_id" name="parent_id">
                <option value="">None</option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" {{ old('parent_id', $coa->parent_id) == $parent->id ? 'selected' : '' }}>{{ $parent->name }} ({{ $parent->code }})</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="level" class="form-label">Level</label>
            <input type="number" class="form-control" id="level" name="level" value="{{ old('level', $coa->level) }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Account</button>
        <a href="{{ route('coa.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection 