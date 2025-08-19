<div class="row mb-3 align-items-end">
    <div class="col-md-4">
        <label for="item_id_{{ $index }}" class="form-label">الصنف</label>
        <select class="form-select" id="item_id_{{ $index }}" name="items[{{ $index }}][item_id]" required onchange="updateItemPrice(this, {{ $index }})">
            <option value="">اختر الصنف</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}" data-cost="{{ $product->standard_cost }}" {{ (isset($item['item_id']) && $item['item_id'] == $product->id) ? 'selected' : '' }}>{{ $product->name }} ({{ $product->code }})</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label for="quantity_{{ $index }}" class="form-label">الكمية</label>
        <input type="number" step="0.01" class="form-control quantity-input" id="quantity_{{ $index }}" name="items[{{ $index }}][quantity]" value="{{ old('items.' . $index . '.quantity', $item['quantity'] ?? 1) }}" required onchange="calculateLineTotal({{ $index }})">
    </div>
    <div class="col-md-2">
        <label for="unit_price_{{ $index }}" class="form-label">سعر الوحدة</label>
        <input type="number" step="0.01" class="form-control unit-price-input" id="unit_price_{{ $index }}" name="items[{{ $index }}][unit_price]" value="{{ old('items.' . $index . '.unit_price', $item['unit_price'] ?? 0) }}" required onchange="calculateLineTotal({{ $index }})">
    </div>
    <div class="col-md-2">
        <label for="line_total_{{ $index }}" class="form-label">المجموع</label>
        <input type="text" class="form-control line-total-display" id="line_total_{{ $index }}" value="0.00" readonly>
    </div>
    <div class="col-md-2">
        <button type="button" class="btn btn-danger btn-sm remove-item-row">حذف</button>
    </div>
</div> 