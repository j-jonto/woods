@extends('layouts.app')

@section('content')
<div class="container">
    <h1>تعديل قائمة المواد (BOM)</h1>

    <form action="{{ route('boms.update', $billOfMaterial->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label for="finished_good_id" class="form-label">المنتج النهائي</label>
            <select class="form-select @error('finished_good_id') is-invalid @enderror" id="finished_good_id" name="finished_good_id" required>
                <option value="">اختر المنتج النهائي</option>
                @foreach ($finishedGoods as $product)
                    <option value="{{ $product->id }}" 
                        {{ old('finished_good_id', $billOfMaterial->finished_good_id) == $product->id ? 'selected' : '' }}>
                        {{ $product->name }} ({{ $product->code }})
                    </option>
                @endforeach
            </select>
            @error('finished_good_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="raw_material_id" class="form-label">المواد الخام</label>
            <select class="form-select @error('raw_material_id') is-invalid @enderror" id="raw_material_id" name="raw_material_id" required>
                <option value="">اختر المواد الخام</option>
                @foreach ($rawMaterials as $material)
                    <option value="{{ $material->id }}" 
                        {{ old('raw_material_id', $billOfMaterial->raw_material_id) == $material->id ? 'selected' : '' }}>
                        {{ $material->name }} ({{ $material->code }})
                    </option>
                @endforeach
            </select>
            @error('raw_material_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="quantity" class="form-label">الكمية المطلوبة من المواد الخام</label>
            <input type="number" step="0.01" class="form-control @error('quantity') is-invalid @enderror" 
                   id="quantity" name="quantity" value="{{ old('quantity', $billOfMaterial->quantity) }}" required>
            @error('quantity')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">الوصف</label>
            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $billOfMaterial->description) }}</textarea>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                   {{ old('is_active', $billOfMaterial->is_active) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">نشطة</label>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">تحديث قائمة المواد</button>
            <a href="{{ route('boms.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // إضافة تأثيرات بصرية
        const selects = document.querySelectorAll('select');
        selects.forEach(select => {
            select.addEventListener('change', function() {
                if (this.value) {
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                }
            });
        });

        // التحقق من صحة الكمية
        const quantityInput = document.getElementById('quantity');
        quantityInput.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (value > 0) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });

        // تحميل القيم الحالية
        if (document.getElementById('finished_good_id').value) {
            document.getElementById('finished_good_id').classList.add('is-valid');
        }
        if (document.getElementById('raw_material_id').value) {
            document.getElementById('raw_material_id').classList.add('is-valid');
        }
        if (parseFloat(document.getElementById('quantity').value) > 0) {
            document.getElementById('quantity').classList.add('is-valid');
        }
    });
</script>
@endpush
@endsection 