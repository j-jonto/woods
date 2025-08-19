@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create New Account</h1>

    <form action="{{ route('coa.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="code" class="form-label">Account Code</label>
            <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Account Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="mb-3">
            <label for="type" class="form-label">Account Type</label>
            <select class="form-select" id="type" name="type" required>
                <option value="">Select Type</option>
                <option value="asset" {{ old('type') == 'asset' ? 'selected' : '' }}>Asset</option>
                <option value="liability" {{ old('type') == 'liability' ? 'selected' : '' }}>Liability</option>
                <option value="equity" {{ old('type') == 'equity' ? 'selected' : '' }}>Equity</option>
                <option value="revenue" {{ old('type') == 'revenue' ? 'selected' : '' }}>Revenue</option>
                <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>Expense</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="parent_id" class="form-label">Parent Account (Optional)</label>
            <select class="form-select" id="parent_id" name="parent_id">
                <option value="">None</option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->name }} ({{ $parent->code }})</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="level" class="form-label">Level</label>
            <input type="number" class="form-control" id="level" name="level" value="{{ old('level', 1) }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Create Account</button>
        <a href="{{ route('coa.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection 