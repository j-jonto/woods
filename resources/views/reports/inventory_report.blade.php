@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">تقرير المخزون</h4>
                </div>
                <div class="card-body">
                    <!-- إحصائيات سريعة -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5>إجمالي الأصناف</h5>
                                    <h3>{{ count($stockData) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5>أصناف منخفضة</h5>
                                    <h3>{{ collect($stockData)->where('is_low_stock', true)->count() }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5>أصناف نافدة</h5>
                                    <h3>{{ collect($stockData)->where('is_out_of_stock', true)->count() }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5>قيمة المخزون</h5>
                                    <h3>{{ number_format(collect($stockData)->sum('stock_value'), 2) }} ريال</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- جدول المخزون -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>الصنف</th>
                                    <th>الفئة</th>
                                    <th>المخزون الحالي</th>
                                    <th>قيمة المخزون</th>
                                    <th>نقطة إعادة الطلب</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockData as $item)
                                    <tr class="{{ $item['is_out_of_stock'] ? 'table-danger' : ($item['is_low_stock'] ? 'table-warning' : '') }}">
                                        <td>{{ $item['item'] }}</td>
                                        <td>{{ $item['category'] }}</td>
                                        <td>
                                            <span class="badge bg-{{ $item['current_stock'] > 0 ? 'success' : 'danger' }}">
                                                {{ $item['current_stock'] }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($item['stock_value'], 2) }} ريال</td>
                                        <td>{{ $item['reorder_point'] }}</td>
                                        <td>
                                            @if($item['is_out_of_stock'])
                                                <span class="badge bg-danger">نافد</span>
                                            @elseif($item['is_low_stock'])
                                                <span class="badge bg-warning">منخفض</span>
                                            @else
                                                <span class="badge bg-success">متوفر</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- تحليل المخزون -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">الأصناف المنخفضة</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>الصنف</th>
                                                    <th>المخزون</th>
                                                    <th>نقطة إعادة الطلب</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(collect($stockData)->where('is_low_stock', true)->take(10) as $item)
                                                    <tr>
                                                        <td>{{ $item['item'] }}</td>
                                                        <td>{{ $item['current_stock'] }}</td>
                                                        <td>{{ $item['reorder_point'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">الأصناف النافدة</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>الصنف</th>
                                                    <th>آخر مخزون</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(collect($stockData)->where('is_out_of_stock', true)->take(10) as $item)
                                                    <tr>
                                                        <td>{{ $item['item'] }}</td>
                                                        <td>{{ $item['current_stock'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 