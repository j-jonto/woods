@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Inventory Transaction Details: {{ $inventoryTransaction->reference_no }}</h1>

    <div class="card mb-4">
        <div class="card-header">
            Transaction Information
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Transaction Date:</strong> {{ \Carbon\Carbon::parse($inventoryTransaction->transaction_date)->format('Y-m-d') }}</p>
                    <p><strong>Reference No.:</strong> {{ $inventoryTransaction->reference_no }}</p>
                    <p><strong>Type:</strong> <span class="badge bg-info">{{ ucfirst($inventoryTransaction->type) }}</span></p>
                    <p><strong>Item:</strong> {{ $inventoryTransaction->item->name }} ({{ $inventoryTransaction->item->code }})</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Warehouse:</strong> {{ $inventoryTransaction->warehouse->name }} ({{ $inventoryTransaction->warehouse->code }})</p>
                    <p><strong>Quantity:</strong> {{ number_format($inventoryTransaction->quantity, 2) }}</p>
                    <p><strong>Unit Cost:</strong> ${{ number_format($inventoryTransaction->unit_cost, 2) }}</p>
                    <p><strong>Batch/Lot No.:</strong> {{ $inventoryTransaction->batch_no ?? 'N/A' }}</p>
                    <p><strong>Description:</strong> {{ $inventoryTransaction->description ?? 'N/A' }}</p>
                    <p><strong>Created By:</strong> {{ $inventoryTransaction->creator->name ?? 'System' }}</p>
                    <p><strong>Last Updated:</strong> {{ $inventoryTransaction->updated_at->format('Y-m-d H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('inventory_transactions.index') }}" class="btn btn-secondary">Back to List</a>
        {{-- Edit is not typical for inventory transactions, but delete might be possible depending on business rules --}}
        {{-- <form action="{{ route('inventory_transactions.destroy', $inventoryTransaction->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this transaction?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete Transaction</button>
        </form> --}}
    </div>
</div>
@endsection 