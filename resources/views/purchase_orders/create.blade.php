@extends('layouts.app')

@section('content')
<div class="container">
    <h1>إنشاء أمر شراء جديد</h1>

    <form action="{{ route('purchase_orders.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="order_no" class="form-label">رقم الطلب</label>
            <input type="text" class="form-control" id="order_no" name="order_no" value="{{ old('order_no') }}" required>
        </div>
        <div class="mb-3">
            <label for="supplier_id" class="form-label">المورد</label>
            <select class="form-select" id="supplier_id" name="supplier_id" required>
                <option value="">اختر المورد</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="order_date" class="form-label">تاريخ الطلب</label>
            <input type="date" class="form-control" id="order_date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required>
        </div>
        <div class="mb-3">
            <label for="delivery_date" class="form-label">تاريخ التسليم المتوقع (اختياري)</label>
            <input type="date" class="form-control" id="delivery_date" name="delivery_date" value="{{ old('delivery_date') }}">
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">ملاحظات</label>
            <textarea class="form-control" id="notes" name="notes">{{ old('notes') }}</textarea>
        </div>
        <div class="mb-3">
            <label for="payment_type" class="form-label">طريقة الدفع</label>
            <select name="payment_type" id="payment_type" class="form-select" required>
                <option value="cash" {{ old('payment_type', 'cash') == 'cash' ? 'selected' : '' }}>نقدًا</option>
                <option value="credit" {{ old('payment_type') == 'credit' ? 'selected' : '' }}>آجل</option>
            </select>
        </div>

        <hr>
        <h3>أصناف الطلب</h3>
        <div id="purchase-order-items-container">
            @if (old('items'))
                @foreach (old('items') as $index => $item)
                    @include('purchase_orders.partials.item_row', ['index' => $index, 'item' => $item, 'products' => $products])
                @endforeach
            @else
                @include('purchase_orders.partials.item_row', ['index' => 0, 'item' => null, 'products' => $products])
            @endif
        </div>
        <button type="button" class="btn btn-info btn-sm mb-3" id="add-item-row">إضافة صنف</button>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">إنشاء أمر الشراء</button>
            <a href="{{ route('purchase_orders.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let itemIndex = {{ old('items') ? count(old('items')) : 1 }};
        document.getElementById('add-item-row').addEventListener('click', function () {
            const container = document.getElementById('purchase-order-items-container');
            const newRow = document.createElement('div');
            newRow.classList.add('row', 'mb-3', 'align-items-end');
            newRow.innerHTML = `
                <div class="col-md-4">
                    <label class="form-label">الصنف</label>
                    <select class="form-select" name="items[${itemIndex}][item_id]" required onchange="updateItemPrice(this, ${itemIndex})">
                        <option value="">اختر الصنف</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" data-cost="{{ $product->standard_cost }}">{{ $product->name }} ({{ $product->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الكمية</label>
                    <input type="number" step="0.01" class="form-control quantity-input" id="quantity_${itemIndex}" name="items[${itemIndex}][quantity]" value="1" required onchange="calculateLineTotal(${itemIndex})">
                </div>
                <div class="col-md-2">
                    <label class="form-label">سعر الوحدة</label>
                    <input type="number" step="0.01" class="form-control unit-cost-input" id="unit_cost_${itemIndex}" name="items[${itemIndex}][unit_cost]" value="0.00" required onchange="calculateLineTotal(${itemIndex})">
                </div>
                <div class="col-md-2">
                    <label class="form-label">المجموع</label>
                    <input type="text" class="form-control line-total-display" id="line_total_${itemIndex}" value="0.00" readonly>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm remove-item-row">حذف</button>
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
        document.querySelectorAll('.quantity-input, .unit-cost-input').forEach(input => {
            input.dispatchEvent(new Event('change'));
        });
    });
</script>
@endpush
@endsection 