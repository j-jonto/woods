@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">تقرير المشتريات</h4>
                </div>
                <div class="card-body">
                    <!-- فلاتر التقرير -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-2">
                                <label for="from" class="form-label">من تاريخ</label>
                                <input type="date" class="form-control" id="from" name="from" value="{{ request('from') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="to" class="form-label">إلى تاريخ</label>
                                <input type="date" class="form-control" id="to" name="to" value="{{ request('to') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="supplier_id" class="form-label">المورد</label>
                                <select class="form-control" id="supplier_id" name="supplier_id">
                                    <option value="">جميع الموردين</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="item_id" class="form-label">الصنف</label>
                                <select class="form-control" id="item_id" name="item_id">
                                    <option value="">جميع الأصناف</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="report_type" class="form-label">نوع التقرير</label>
                                <select class="form-control" id="report_type" name="report_type">
                                    <option value="summary" {{ request('report_type', 'summary') == 'summary' ? 'selected' : '' }}>إجمالي</option>
                                    <option value="detail" {{ request('report_type') == 'detail' ? 'selected' : '' }}>تفصيلي</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block">تطبيق الفلتر</button>
                            </div>
                        </div>
                    </form>

                    @if($report_type == 'summary')
                        <!-- تقرير إجمالي -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>المورد</th>
                                        <th>إجمالي المشتريات</th>
                                        <th>عدد الطلبات</th>
                                        <th>متوسط قيمة الطلب</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($summary as $item)
                                        <tr>
                                            <td>{{ $item->supplier->name ?? 'غير محدد' }}</td>
                                            <td>{{ number_format($item->total_purchases, 2) }} ريال</td>
                                            <td>{{ $item->orders_count }}</td>
                                            <td>{{ number_format($item->total_purchases / $item->orders_count, 2) }} ريال</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- تقرير تفصيلي -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>رقم الطلب</th>
                                        <th>المورد</th>
                                        <th>الصنف</th>
                                        <th>الكمية</th>
                                        <th>سعر الوحدة</th>
                                        <th>المبلغ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($details as $item)
                                        <tr>
                                            <td>{{ $item->order_date }}</td>
                                            <td>{{ $item->order_no }}</td>
                                            <td>{{ $item->supplier_name }}</td>
                                            <td>{{ $item->item_name }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ number_format($item->unit_price, 2) }} ريال</td>
                                            <td>{{ number_format($item->amount, 2) }} ريال</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 