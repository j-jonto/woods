@extends('layouts.app')

@section('content')
<div class="container">
    <h1>إنشاء قيد محاسبي</h1>

    <form action="{{ route('journal_entries.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="entry_date" class="form-label">تاريخ القيد</label>
            <input type="date" class="form-control" id="entry_date" name="entry_date" value="{{ old('entry_date', date('Y-m-d')) }}" required>
        </div>
        <div class="mb-3">
            <label for="reference_no" class="form-label">رقم المرجع</label>
            <input type="text" class="form-control" id="reference_no" name="reference_no" value="{{ old('reference_no') }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">الوصف</label>
            <textarea class="form-control" id="description" name="description">{{ old('description') }}</textarea>
        </div>

        <hr>
        <h3>بنود القيد</h3>
        <div id="journal-items-container">
            @if (old('items'))
                @foreach (old('items') as $index => $item)
                    @include('journal_entries.partials.item_row', ['index' => $index, 'item' => $item, 'accounts' => $accounts])
                @endforeach
            @else
                @include('journal_entries.partials.item_row', ['index' => 0, 'item' => null, 'accounts' => $accounts])
                @include('journal_entries.partials.item_row', ['index' => 1, 'item' => null, 'accounts' => $accounts])
            @endif
        </div>
        <button type="button" class="btn btn-info btn-sm mb-3" id="add-item-row">إضافة بند</button>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">حفظ القيد</button>
            <a href="{{ route('journal_entries.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let itemIndex = {{ old('items') ? count(old('items')) : 2 }};
        document.getElementById('add-item-row').addEventListener('click', function () {
            const container = document.getElementById('journal-items-container');
            const newRow = document.createElement('div');
            newRow.classList.add('row', 'mb-3', 'align-items-end');
            newRow.innerHTML = `
                <div class="col-md-4">
                    <label class="form-label">الحساب</label>
                    <select class="form-select" name="items[${itemIndex}][account_id]" required>
                        <option value="">اختر الحساب</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">مدين</label>
                    <input type="number" step="0.01" class="form-control" name="items[${itemIndex}][debit]" value="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label">دائن</label>
                    <input type="number" step="0.01" class="form-control" name="items[${itemIndex}][credit]" value="0">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm remove-item-row">حذف</button>
                </div>
            `;
            container.appendChild(newRow);
            itemIndex++;
            attachRemoveListeners();
        });

        function attachRemoveListeners() {
            document.querySelectorAll('.remove-item-row').forEach(button => {
                button.onclick = function() {
                    this.closest('.row').remove();
                };
            });
        }

        attachRemoveListeners();
    });
</script>
@endpush
@endsection 