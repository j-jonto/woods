@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>تقارير المبيعات</h1>
        <a href="{{ route('print.sales_report') }}" class="btn btn-success" target="_blank">
            <i class="fas fa-print"></i> طباعة التقرير
        </a>
    </div>
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label">العميل</label>
            <select name="customer_id" class="form-select">
                <option value="">كل العملاء</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">الصنف</label>
            <select name="item_id" class="form-select">
                <option value="">كل الأصناف</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }} ({{ $item->code }})</option>
                @endforeach
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
        <div class="col-md-2">
            <label class="form-label">نوع التقرير</label>
            <select name="report_type" class="form-select">
                <option value="summary" {{ request('report_type', 'summary') == 'summary' ? 'selected' : '' }}>إجمالي</option>
                <option value="detail" {{ request('report_type') == 'detail' ? 'selected' : '' }}>تفصيلي</option>
            </select>
        </div>
        <div class="col-md-12 text-end">
            <button type="submit" class="btn btn-primary">عرض التقرير</button>
        </div>
    </form>
    @if($report_type == 'summary')
        <h5 class="mb-3">تقرير مبيعات إجمالي</h5>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>العميل</th>
                    <th class="text-end">إجمالي المبيعات</th>
                    <th class="text-end">عدد الطلبات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($summary as $row)
                    <tr>
                        <td>{{ $row->customer->name }}</td>
                        <td class="text-end">{{ number_format($row->total_sales, 2) }}</td>
                        <td class="text-end">{{ $row->orders_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">لا توجد بيانات</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif($report_type == 'detail')
        <h5 class="mb-3">تقرير مبيعات تفصيلي</h5>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>تاريخ الطلب</th>
                    <th>رقم الطلب</th>
                    <th>العميل</th>
                    <th>الصنف</th>
                    <th class="text-end">الكمية</th>
                    <th class="text-end">سعر الوحدة</th>
                    <th class="text-end">المجموع</th>
                </tr>
            </thead>
            <tbody>
                @forelse($details as $row)
                    <tr>
                        <td>{{ $row->order_date }}</td>
                        <td>{{ $row->order_no }}</td>
                        <td>{{ $row->customer_name }}</td>
                        <td>{{ $row->item_name }}</td>
                        <td class="text-end">{{ number_format($row->quantity, 2) }}</td>
                        <td class="text-end">{{ number_format($row->unit_price, 2) }}</td>
                        <td class="text-end">{{ number_format($row->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">لا توجد بيانات</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif
</div>
@endsection 