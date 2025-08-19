@extends('layouts.app')

@section('content')
<div class="container">
    <h1>تعديل أمر البيع: {{ $salesOrder->order_no }}</h1>

    <form action="{{ route('sales_orders.update', $salesOrder->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="order_no" class="form-label">رقم الطلب</label>
            <input type="text" class="form-control" id="order_no" name="order_no" value="{{ old('order_no', $salesOrder->order_no) }}" required>
        </div>
        <div class="mb-3">
            <label for="customer_id" class="form-label">العميل</label>
            <select class="form-select" id="customer_id" name="customer_id" required>
                <option value="">اختر العميل</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id', $salesOrder->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="order_date" class="form-label">تاريخ الطلب</label>
            <input type="date" class="form-control" id="order_date" name="order_date" value="{{ old('order_date', $salesOrder->order_date->format('Y-m-d')) }}" required>
        </div>
        <div class="mb-3">
            <label for="delivery_date" class="form-label">تاريخ التسليم (اختياري)</label>
            <input type="date" class="form-control" id="delivery_date" name="delivery_date" value="{{ old('delivery_date', $salesOrder->delivery_date ? $salesOrder->delivery_date->format('Y-m-d') : '') }}">
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">ملاحظات</label>
            <textarea class="form-control" id="notes" name="notes">{{ old('notes', $salesOrder->notes) }}</textarea>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">الحالة</label>
            <select class="form-select" id="status" name="status" required>
                <option value="draft" {{ old('status', $salesOrder->status) == 'draft' ? 'selected' : '' }}>مسودة</option>
                <option value="pending" {{ old('status', $salesOrder->status) == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                <option value="confirmed" {{ old('status', $salesOrder->status) == 'confirmed' ? 'selected' : '' }}>مؤكد</option>
                <option value="shipped" {{ old('status', $salesOrder->status) == 'shipped' ? 'selected' : '' }}>تم الشحن</option>
                <option value="delivered" {{ old('status', $salesOrder->status) == 'delivered' ? 'selected' : '' }}>تم التسليم</option>
                <option value="invoiced" {{ old('status', $salesOrder->status) == 'invoiced' ? 'selected' : '' }}>تم الفوترة</option>
                <option value="paid" {{ old('status', $salesOrder->status) == 'paid' ? 'selected' : '' }}>تم الدفع</option>
                <option value="cancelled" {{ old('status', $salesOrder->status) == 'cancelled' ? 'selected' : '' }}>ملغي</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="payment_type" class="form-label">طريقة الدفع</label>
            <select name="payment_type" id="payment_type" class="form-select" required>
                <option value="cash" {{ old('payment_type', $salesOrder->payment_type ?? 'cash') == 'cash' ? 'selected' : '' }}>نقدًا</option>
                <option value="credit" {{ old('payment_type', $salesOrder->payment_type ?? 'cash') == 'credit' ? 'selected' : '' }}>آجل</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="representative_id" class="form-label">مندوب المبيعات</label>
            <select name="representative_id" id="representative_id" class="form-select">
                <option value="">اختر مندوب المبيعات (اختياري)</option>
                @foreach(\App\Models\SalesRepresentative::where('is_active', true)->get() as $rep)
                    <option value="{{ $rep->id }}" {{ old('representative_id', $salesOrder->representative_id) == $rep->id ? 'selected' : '' }}>
                        {{ $rep->name }} ({{ $rep->commission_rate }}%)
                    </option>
                @endforeach
            </select>
        </div>

        <hr>
        <h3>أصناف الطلب</h3>
        <div id="sales-order-items-container">
            @foreach ($salesOrder->items as $index => $item)
                @include('sales_orders.partials.item_row', ['index' => $index, 'item' => $item, 'products' => $products])
            @endforeach
            @if (!old('items') && $salesOrder->items->isEmpty())
                {{-- Add empty row if no items exist and not coming from a validation error --}}
                @include('sales_orders.partials.item_row', ['index' => 0, 'item' => null, 'products' => $products])
            @endif
        </div>
        <button type="button" class="btn btn-info btn-sm mb-3" id="add-item-row">إضافة صنف</button>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">تحديث أمر البيع</button>
            <a href="{{ route('sales_orders.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let itemIndex = {{ old('items') ? count(old('items')) : $salesOrder->items->count() }};
        if (itemIndex === 0) itemIndex = 1; // Ensure at least 1 for initial empty row

        document.getElementById('add-item-row').addEventListener('click', function () {
            const container = document.getElementById('sales-order-items-container');
            const newRow = document.createElement('div');
            newRow.classList.add('row', 'mb-3', 'align-items-end');
            newRow.innerHTML = `
                <div class="col-md-4">
                    <label class="form-label">المنتج</label>
                    <select class="form-select" name="items[${itemIndex}][item_id]" required onchange="updateItemPrice(this, ${itemIndex})">
                        <option value="">اختر المنتج</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->selling_price }}">{{ $product->name }} ({{ $product->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الكمية</label>
                    <input type="number" step="0.01" class="form-control quantity-input" id="quantity_${itemIndex}" name="items[${itemIndex}][quantity]" value="1" required onchange="calculateLineTotal(${itemIndex})">
                </div>
                <div class="col-md-2">
                    <label class="form-label">سعر الوحدة</label>
                    <input type="number" step="0.01" class="form-control unit-price-input" id="unit_price_${itemIndex}" name="items[${itemIndex}][unit_price]" value="0.00" required onchange="calculateLineTotal(${itemIndex})">
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
            const price = selectedOption.dataset.price || '0.00';
            document.getElementById(`unit_price_${index}`).value = parseFloat(price).toFixed(2);
            calculateLineTotal(index);
        }

        function calculateLineTotal(index) {
            const quantity = parseFloat(document.getElementById(`quantity_${index}`).value);
            const unitPrice = parseFloat(document.getElementById(`unit_price_${index}`).value);
            const lineTotal = (quantity * unitPrice).toFixed(2);
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
        document.querySelectorAll('.quantity-input, .unit-price-input').forEach(input => {
            input.dispatchEvent(new Event('change'));
        });
    });
</script>
@endpush
@endsection 