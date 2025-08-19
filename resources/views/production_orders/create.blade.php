@extends('layouts.app')

@section('content')
<div class="container">
    <h1>إنشاء أمر إنتاج جديد</h1>

    <form action="{{ route('production_orders.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="order_no" class="form-label">رقم الأمر</label>
            <input type="text" class="form-control @error('order_no') is-invalid @enderror" 
                   id="order_no" name="order_no" value="{{ old('order_no') }}" required>
            @error('order_no')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label for="item_id" class="form-label">المنتج المراد إنتاجه</label>
            <select class="form-select @error('item_id') is-invalid @enderror" id="item_id" name="item_id" required>
                <option value="">اختر المنتج</option>
                @foreach ($finishedGoods as $product)
                    <option value="{{ $product->id }}" {{ old('item_id') == $product->id ? 'selected' : '' }}>
                        {{ $product->name }} ({{ $product->code }})
                    </option>
                @endforeach
            </select>
            @error('item_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label for="bill_of_material_id" class="form-label">قائمة المواد (BOM) - اختياري</label>
            <select class="form-select" id="bill_of_material_id" name="bill_of_material_id">
                <option value="">اختر قائمة المواد (اختياري)</option>
                @foreach ($boms as $bom)
                    @if($bom->finishedGood && $bom->rawMaterial)
                        <option value="{{ $bom->id }}" 
                                data-product-id="{{ $bom->finished_good_id }}" 
                                data-bom-quantity="{{ $bom->quantity }}"
                                {{ old('bill_of_material_id') == $bom->id ? 'selected' : '' }}>
                            {{ $bom->finishedGood->name }} 
                            (يحتاج: {{ $bom->quantity }} {{ $bom->rawMaterial->unit_of_measure }} من {{ $bom->rawMaterial->name }})
                        </option>
                    @endif
                @endforeach
            </select>
            <div class="form-text">اختيار قائمة المواد ضروري لبدء الإنتاج وخصم المواد الخام من المخزون</div>
        </div>
        
        <div class="mb-3">
            <label for="quantity" class="form-label">الكمية المراد إنتاجها</label>
            <input type="number" step="0.01" class="form-control @error('quantity') is-invalid @enderror" 
                   id="quantity" name="quantity" value="{{ old('quantity', 1) }}" required>
            @error('quantity')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label for="start_date" class="form-label">تاريخ البدء</label>
            <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                   id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required>
            @error('start_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label for="end_date" class="form-label">تاريخ الانتهاء (اختياري)</label>
            <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                   id="end_date" name="end_date" value="{{ old('end_date') }}">
            @error('end_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-3">
            <label for="work_center_id" class="form-label">مركز العمل (اختياري)</label>
            <select class="form-select" id="work_center_id" name="work_center_id">
                <option value="">اختر مركز العمل</option>
                @foreach ($workCenters as $workCenter)
                    <option value="{{ $workCenter->id }}" {{ old('work_center_id') == $workCenter->id ? 'selected' : '' }}>
                        {{ $workCenter->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="mb-3">
            <label for="notes" class="form-label">ملاحظات</label>
            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
        </div>
        
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">إنشاء أمر الإنتاج</button>
            <a href="{{ route('production_orders.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const itemSelect = document.getElementById('item_id');
    const bomSelect = document.getElementById('bill_of_material_id');
    const quantityInput = document.getElementById('quantity');
    
    // تحديث قوائم المواد عند اختيار المنتج
    itemSelect.addEventListener('change', function() {
        const selectedItemId = this.value;
        
        // إعادة تعيين قائمة المواد
        bomSelect.value = '';
        
        // إذا تم اختيار منتج، عرض قوائم المواد المتعلقة به
        if (selectedItemId) {
            Array.from(bomSelect.options).forEach(option => {
                if (option.value && option.dataset.productId === selectedItemId) {
                    option.style.display = '';
                } else if (option.value) {
                    option.style.display = 'none';
                }
            });
        } else {
            // إظهار جميع الخيارات إذا لم يتم اختيار منتج
            Array.from(bomSelect.options).forEach(option => {
                option.style.display = '';
            });
        }
    });
    
    // تحديث معلومات الكمية عند اختيار قائمة المواد
    bomSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const bomQuantity = parseFloat(selectedOption.dataset.bomQuantity || 1);
            quantityInput.value = bomQuantity;
        }
    });
    
    // التحقق من صحة التواريخ
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    endDateInput.addEventListener('change', function() {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(this.value);
        
        if (endDate < startDate) {
            alert('تاريخ الانتهاء يجب أن يكون بعد تاريخ البدء');
            this.value = '';
        }
    });
});
</script>
@endpush
@endsection 