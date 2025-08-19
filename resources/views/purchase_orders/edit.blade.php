@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Purchase Order: {{ $purchaseOrder->order_no }}</h1>

    <form action="{{ route('purchase_orders.update', $purchaseOrder->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="order_no" class="form-label">Order No.</label>
            <input type="text" class="form-control" id="order_no" name="order_no" value="{{ old('order_no', $purchaseOrder->order_no) }}" required>
        </div>
        <div class="mb-3">
            <label for="supplier_id" class="form-label">Supplier</label>
            <select class="form-select" id="supplier_id" name="supplier_id" required>
                <option value="">Select Supplier</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchaseOrder->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="order_date" class="form-label">Order Date</label>
            <input type="date" class="form-control" id="order_date" name="order_date" value="{{ old('order_date', $purchaseOrder->order_date->format('Y-m-d')) }}" required>
        </div>
        <div class="mb-3">
            <label for="delivery_date" class="form-label">Expected Delivery Date (Optional)</label>
            <input type="date" class="form-control" id="delivery_date" name="delivery_date" value="{{ old('delivery_date', $purchaseOrder->delivery_date ? $purchaseOrder->delivery_date->format('Y-m-d') : '') }}">
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes">{{ old('notes', $purchaseOrder->notes) }}</textarea>
        </div>
        <div class="mb-3">
            <label for="payment_type" class="form-label">طريقة الدفع</label>
            <select name="payment_type" id="payment_type" class="form-select" required>
                <option value="cash" {{ old('payment_type', $purchaseOrder->payment_type ?? 'cash') == 'cash' ? 'selected' : '' }}>نقدًا</option>
                <option value="credit" {{ old('payment_type', $purchaseOrder->payment_type ?? 'cash') == 'credit' ? 'selected' : '' }}>آجل</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
                <option value="draft" {{ old('status', $purchaseOrder->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="pending" {{ old('status', $purchaseOrder->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="confirmed" {{ old('status', $purchaseOrder->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="received" {{ old('status', $purchaseOrder->status) == 'received' ? 'selected' : '' }}>Received</option>
                <option value="invoiced" {{ old('status', $purchaseOrder->status) == 'invoiced' ? 'selected' : '' }}>Invoiced</option>
                <option value="paid" {{ old('status', $purchaseOrder->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="cancelled" {{ old('status', $purchaseOrder->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <hr>
        <h3>Order Items</h3>
        <div id="purchase-order-items-container">
            @foreach ($purchaseOrder->items as $index => $item)
                @include('purchase_orders.partials.item_row', ['index' => $index, 'item' => $item, 'products' => $products])
            @endforeach
            @if (!old('items') && $purchaseOrder->items->isEmpty())
                {{-- Add empty row if no items exist and not coming from a validation error --}}
                @include('purchase_orders.partials.item_row', ['index' => 0, 'item' => null, 'products' => $products])
            @endif
        </div>
        <button type="button" class="btn btn-info btn-sm mb-3" id="add-item-row">Add Item</button>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Update Purchase Order</button>
            <a href="{{ route('purchase_orders.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let itemIndex = {{ old('items') ? count(old('items')) : $purchaseOrder->items->count() }};
        if (itemIndex === 0) itemIndex = 1; // Ensure at least 1 for initial empty row

        document.getElementById('add-item-row').addEventListener('click', function () {
            const container = document.getElementById('purchase-order-items-container');
            const newRow = document.createElement('div');
            newRow.classList.add('row', 'mb-3', 'align-items-end');
            newRow.innerHTML = `
                <div class="col-md-4">
                    <label class="form-label">Item</label>
                    <select class="form-select" name="items[${itemIndex}][item_id]" required onchange="updateItemPrice(this, ${itemIndex})">
                        <option value="">Select Item</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" data-cost="{{ $product->standard_cost }}">{{ $product->name }} ({{ $product->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quantity</label>
                    <input type="number" step="0.01" class="form-control quantity-input" id="quantity_${itemIndex}" name="items[${itemIndex}][quantity]" value="1" required onchange="calculateLineTotal(${itemIndex})">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unit Cost</label>
                    <input type="number" step="0.01" class="form-control unit-cost-input" id="unit_cost_${itemIndex}" name="items[${itemIndex}][unit_cost]" value="0.00" required onchange="calculateLineTotal(${itemIndex})">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Line Total</label>
                    <input type="text" class="form-control line-total-display" id="line_total_${itemIndex}" value="0.00" readonly>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm remove-item-row">Remove</button>
                </div>
            `;
            container.appendChild(newRow);
            itemIndex++;
            attachRemoveListeners();
        });

        function updateItemPrice(selectElement, index) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const cost = selectedOption.dataset.cost || '0.00';
            document.getElementById(`unit_cost_${index}`).value = parseFloat(cost).toFixed(2);
            calculateLineTotal(index);
        }

        function calculateLineTotal(index) {
            const quantity = parseFloat(document.getElementById(`quantity_${index}`).value);
            const unitCost = parseFloat(document.getElementById(`unit_cost_${index}`).value);
            const lineTotal = (quantity * unitCost).toFixed(2);
            document.getElementById(`line_total_${index}`).value = lineTotal;
        }

        function attachRemoveListeners() {
            document.querySelectorAll('.remove-item-row').forEach(button => {
                button.onclick = function() {
                    this.closest('.row').remove();
                };
            });
        }

        attachRemoveListeners();
        // Recalculate totals on load for existing items (important for old input values)
        document.querySelectorAll('.quantity-input, .unit-cost-input').forEach(input => {
            input.dispatchEvent(new Event('change'));
        });
    });
</script>
@endpush
@endsection 