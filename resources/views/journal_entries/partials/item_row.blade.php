<div class="row mb-3 align-items-end">
    <div class="col-md-4">
        <label for="account_id_{{ $index }}" class="form-label">Account</label>
        <select class="form-select" id="account_id_{{ $index }}" name="items[{{ $index }}][account_id]" required>
            <option value="">Select Account</option>
            @foreach ($accounts as $account)
                <option value="{{ $account->id }}" {{ (isset($item['account_id']) && $item['account_id'] == $account->id) ? 'selected' : '' }}>{{ $account->name }} ({{ $account->code }})</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label for="debit_{{ $index }}" class="form-label">Debit</label>
        <input type="number" step="0.01" class="form-control" id="debit_{{ $index }}" name="items[{{ $index }}][debit]" value="{{ old('items.' . $index . '.debit', $item['debit'] ?? 0) }}">
    </div>
    <div class="col-md-3">
        <label for="credit_{{ $index }}" class="form-label">Credit</label>
        <input type="number" step="0.01" class="form-control" id="credit_{{ $index }}" name="items[{{ $index }}][credit]" value="{{ old('items.' . $index . '.credit', $item['credit'] ?? 0) }}">
    </div>
    <div class="col-md-2">
        <button type="button" class="btn btn-danger btn-sm remove-item-row">Remove</button>
    </div>
</div> 