@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>تقارير المخزن</h1>
        <a href="{{ route('print.inventory_report') }}" class="btn btn-success" target="_blank">
            <i class="fas fa-print"></i> طباعة التقرير
        </a>
    </div>
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label">المخزن</label>
            <select name="warehouse_id" class="form-select">
                <option value="">كل المخازن</option>
                @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">نوع التقرير</label>
            <select name="report_type" class="form-select">
                <option value="stock" {{ request('report_type', 'stock') == 'stock' ? 'selected' : '' }}>تقرير الكميات المتوفرة</option>
                <option value="reorder" {{ request('report_type') == 'reorder' ? 'selected' : '' }}>الأصناف الناقصة</option>
                <option value="movement" {{ request('report_type') == 'movement' ? 'selected' : '' }}>حركة المخزون خلال فترة</option>
            </select>
        </div>
        <div class="col-md-3" id="date-range" style="display: {{ request('report_type') == 'movement' ? 'block' : 'none' }};">
            <label class="form-label">من تاريخ</label>
            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
        </div>
        <div class="col-md-3" id="date-range2" style="display: {{ request('report_type') == 'movement' ? 'block' : 'none' }};">
            <label class="form-label">إلى تاريخ</label>
            <input type="date" name="to" class="form-control" value="{{ request('to') }}">
        </div>
        <div class="col-md-12 text-end">
            <button type="submit" class="btn btn-primary">عرض التقرير</button>
        </div>
    </form>
    @if($report_type == 'stock')
        <h5 class="mb-3">تقرير الكميات المتوفرة</h5>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>الصنف</th>
                    <th>المخزن</th>
                    <th class="text-end">الكمية المتوفرة</th>
                    <th class="text-end">نقطة إعادة الطلب</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stock as $row)
                    <tr>
                        <td>{{ $row->item->name }} ({{ $row->item->code }})</td>
                        <td>{{ $row->warehouse->name }}</td>
                        <td class="text-end">{{ number_format($row->quantity, 2) }}</td>
                        <td class="text-end">{{ number_format($row->item->reorder_point, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">لا توجد بيانات</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($report_type == 'reorder')
        <h5 class="mb-3">الأصناف الناقصة</h5>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>الصنف</th>
                    <th>المخزن</th>
                    <th class="text-end">الكمية المتوفرة</th>
                    <th class="text-end">نقطة إعادة الطلب</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reorder as $row)
                    <tr>
                        <td>{{ $row->item->name }} ({{ $row->item->code }})</td>
                        <td>{{ $row->warehouse->name }}</td>
                        <td class="text-end">{{ number_format($row->quantity, 2) }}</td>
                        <td class="text-end">{{ number_format($row->item->reorder_point, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">لا توجد بيانات</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($report_type == 'movement')
        <h5 class="mb-3">حركة المخزون خلال فترة</h5>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>نوع الحركة</th>
                    <th>الصنف</th>
                    <th>المخزن</th>
                    <th class="text-end">الكمية</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row->transaction_date)->format('Y-m-d') }}</td>
                        <td><span class="badge bg-info">{{ __($row->type) }}</span></td>
                        <td>{{ $row->item->name }} ({{ $row->item->code }})</td>
                        <td>{{ $row->warehouse->name }}</td>
                        <td class="text-end">{{ number_format($row->quantity, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">لا توجد بيانات</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif
</div>
@endsection 