@extends('prints.layout')

@section('content')
    @php
        $title = 'تقرير المخزون';
    @endphp

    <div class="document-info">
        <div>
            <div class="info-item">
                <span class="info-label">تاريخ التقرير:</span>
                <span>{{ now()->format('Y-m-d') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">عدد الأصناف:</span>
                <span>{{ $stock->count() }}</span>
            </div>
        </div>
        <div>
            <div class="info-item">
                <span class="info-label">إجمالي الكميات:</span>
                <span>{{ number_format($stock->sum('quantity'), 2) }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">عدد المخازن:</span>
                <span>{{ $stock->unique('warehouse_id')->count() }}</span>
            </div>
        </div>
    </div>

    <div style="margin-bottom: 30px;">
        <h4>ملخص المخزون</h4>
        <table class="table">
            <tr>
                <td><strong>إجمالي الكميات:</strong></td>
                <td class="text-right">{{ number_format($stock->sum('quantity'), 2) }}</td>
            </tr>
            <tr>
                <td><strong>عدد الأصناف:</strong></td>
                <td class="text-right">{{ $stock->count() }}</td>
            </tr>
            <tr>
                <td><strong>عدد المخازن:</strong></td>
                <td class="text-right">{{ $stock->unique('warehouse_id')->count() }}</td>
            </tr>
            <tr>
                <td><strong>متوسط الكمية:</strong></td>
                <td class="text-right">{{ $stock->count() > 0 ? number_format($stock->avg('quantity'), 2) : '0.00' }}</td>
            </tr>
        </table>
    </div>

    <div>
        <h4>تفاصيل المخزون</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>الصنف</th>
                    <th>الكود</th>
                    <th>المخزن</th>
                    <th>الكمية المتوفرة</th>
                    <th>نقطة إعادة الطلب</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stock as $item)
                    <tr>
                        <td>{{ $item->item ? $item->item->name : '-' }}</td>
                        <td>{{ $item->item ? $item->item->code : '-' }}</td>
                        <td>{{ $item->warehouse ? $item->warehouse->name : '-' }}</td>
                        <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-center">{{ $item->item ? number_format($item->item->reorder_point, 2) : '-' }}</td>
                        <td>
                            @if($item->item && $item->quantity <= $item->item->reorder_point)
                                <span style="color: red; font-weight: bold;">ناقص</span>
                            @elseif($item->quantity > 0)
                                <span style="color: green; font-weight: bold;">متوفر</span>
                            @else
                                <span style="color: orange; font-weight: bold;">غير متوفر</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">لا توجد أصناف في المخزون</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 30px;">
        <h4>توزيع المخزون حسب المخازن</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>المخزن</th>
                    <th>عدد الأصناف</th>
                    <th>إجمالي الكميات</th>
                    <th>متوسط الكمية</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $stockByWarehouse = $stock->groupBy('warehouse_id');
                @endphp
                @forelse($stockByWarehouse as $warehouseId => $warehouseStock)
                    @php
                        $warehouse = $warehouseStock->first()->warehouse;
                        $warehouseTotal = $warehouseStock->sum('quantity');
                        $warehouseAvg = $warehouseStock->avg('quantity');
                    @endphp
                    <tr>
                        <td>{{ $warehouse ? $warehouse->name : 'غير محدد' }}</td>
                        <td class="text-center">{{ $warehouseStock->count() }}</td>
                        <td class="text-right">{{ number_format($warehouseTotal, 2) }}</td>
                        <td class="text-right">{{ number_format($warehouseAvg, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">لا توجد بيانات</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 30px;">
        <h4>الأصناف الناقصة (تحتاج إعادة طلب)</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>الصنف</th>
                    <th>الكود</th>
                    <th>المخزن</th>
                    <th>الكمية المتوفرة</th>
                    <th>نقطة إعادة الطلب</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $lowStock = $stock->filter(function($item) {
                        return $item->item && $item->quantity <= $item->item->reorder_point;
                    });
                @endphp
                @forelse($lowStock as $item)
                    <tr style="background-color: #ffe6e6;">
                        <td>{{ $item->item ? $item->item->name : '-' }}</td>
                        <td>{{ $item->item ? $item->item->code : '-' }}</td>
                        <td>{{ $item->warehouse ? $item->warehouse->name : '-' }}</td>
                        <td class="text-center" style="color: red; font-weight: bold;">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-center">{{ $item->item ? number_format($item->item->reorder_point, 2) : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">لا توجد أصناف ناقصة</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">توقيع أمين المخزن</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">توقيع المدير</div>
        </div>
    </div>
@endsection 