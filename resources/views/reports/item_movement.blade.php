@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>تتبع حركة الصنف</h1>
        <a href="{{ route('print.inventory_report') }}" class="btn btn-success" target="_blank">
            <i class="fas fa-print"></i> طباعة التقرير
        </a>
    </div>
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label">الصنف</label>
            <select name="item_id" class="form-select">
                <option value="">كل الأصناف</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }} ({{ $item->code }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">المخزن</label>
            <select name="warehouse_id" class="form-select">
                <option value="">كل المخازن</option>
                @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">نوع الحركة</label>
            <select name="type" class="form-select">
                <option value="">الكل</option>
                <option value="receipt" {{ request('type') == 'receipt' ? 'selected' : '' }}>إدخال</option>
                <option value="issue" {{ request('type') == 'issue' ? 'selected' : '' }}>إخراج</option>
                <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>تحويل</option>
                <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>تسوية</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">من تاريخ</label>
            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">إلى تاريخ</label>
            <input type="date" name="to" class="form-control" value="{{ request('to') }}">
        </div>
        <div class="col-md-12 text-end">
            <button type="submit" class="btn btn-primary">بحث</button>
        </div>
    </form>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>نوع الحركة</th>
                <th>الصنف</th>
                <th>المخزن</th>
                <th class="text-end">الكمية</th>
                <th class="text-end">تكلفة الوحدة</th>
                <th>رقم المرجع</th>
                <th>أنشئ بواسطة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d') }}</td>
                    <td><span class="badge bg-info">{{ __($transaction->type) }}</span></td>
                    <td>{{ $transaction->item->name }} ({{ $transaction->item->code }})</td>
                    <td>{{ $transaction->warehouse->name }}</td>
                    <td class="text-end">{{ number_format($transaction->quantity, 2) }}</td>
                    <td class="text-end">{{ number_format($transaction->unit_cost, 2) }}</td>
                    <td>{{ $transaction->reference_no }}</td>
                    <td>{{ $transaction->creator->name ?? 'النظام' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">لا توجد بيانات</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{ $transactions->withQueryString()->links() }}
</div>
@endsection 