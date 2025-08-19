@extends('prints.layout')

@section('content')
    @php
        $title = 'تقرير المبيعات';
    @endphp

    <div class="document-info">
        <div>
            <div class="info-item">
                <span class="info-label">من تاريخ:</span>
                <span>{{ $from_date }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">إلى تاريخ:</span>
                <span>{{ $to_date }}</span>
            </div>
        </div>
        <div>
            <div class="info-item">
                <span class="info-label">عدد الطلبات:</span>
                <span>{{ $sales->count() }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">إجمالي المبيعات:</span>
                <span class="amount">{{ number_format($sales->sum('total_amount'), 2) }} دينار ليبي</span>
            </div>
        </div>
    </div>

    <div style="margin-bottom: 30px;">
        <h4>ملخص المبيعات</h4>
        <table class="table">
            <tr>
                <td><strong>إجمالي المبيعات:</strong></td>
                <td class="text-right amount">{{ number_format($sales->sum('total_amount'), 2) }} دينار ليبي</td>
            </tr>
            <tr>
                <td><strong>عدد الطلبات:</strong></td>
                <td class="text-right">{{ $sales->count() }}</td>
            </tr>
            <tr>
                <td><strong>متوسط قيمة الطلب:</strong></td>
                <td class="text-right">{{ $sales->count() > 0 ? number_format($sales->avg('total_amount'), 2) : '0.00' }} دينار ليبي</td>
            </tr>
            <tr>
                <td><strong>عدد العملاء:</strong></td>
                <td class="text-right">{{ $sales->unique('customer_id')->count() }}</td>
            </tr>
        </table>
    </div>

    <div>
        <h4>تفاصيل المبيعات</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>رقم الطلب</th>
                    <th>العميل</th>
                    <th>المبلغ الإجمالي</th>
                    <th>الحالة</th>
                    <th>الملاحظات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <td>{{ $sale->order_date }}</td>
                        <td>{{ $sale->order_no }}</td>
                        <td>{{ $sale->customer ? $sale->customer->name : '-' }}</td>
                        <td class="text-right amount">{{ number_format($sale->total_amount, 2) }} دينار ليبي</td>
                        <td>{{ $sale->status }}</td>
                        <td>{{ $sale->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">لا توجد مبيعات في هذه الفترة</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3"><strong>المجموع</strong></td>
                    <td class="text-right amount"><strong>{{ number_format($sales->sum('total_amount'), 2) }} دينار ليبي</strong></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div style="margin-top: 30px;">
        <h4>توزيع المبيعات حسب العملاء</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>العميل</th>
                    <th>عدد الطلبات</th>
                    <th>إجمالي المبيعات</th>
                    <th>متوسط قيمة الطلب</th>
                    <th>النسبة المئوية</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalSales = $sales->sum('total_amount');
                    $salesByCustomer = $sales->groupBy('customer_id');
                @endphp
                @forelse($salesByCustomer as $customerId => $customerSales)
                    @php
                        $customer = $customerSales->first()->customer;
                        $customerTotal = $customerSales->sum('total_amount');
                        $customerAvg = $customerSales->avg('total_amount');
                        $percentage = $totalSales > 0 ? ($customerTotal / $totalSales) * 100 : 0;
                    @endphp
                    <tr>
                        <td>{{ $customer ? $customer->name : 'غير محدد' }}</td>
                        <td class="text-center">{{ $customerSales->count() }}</td>
                        <td class="text-right amount">{{ number_format($customerTotal, 2) }} دينار ليبي</td>
                        <td class="text-right">{{ number_format($customerAvg, 2) }} دينار ليبي</td>
                        <td class="text-center">{{ number_format($percentage, 1) }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">لا توجد مبيعات</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sales->count() > 0)
        <div style="margin-top: 30px;">
            <h4>تفاصيل الأصناف المباعة</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>الصنف</th>
                        <th>الكمية المباعة</th>
                        <th>إجمالي المبيعات</th>
                        <th>متوسط السعر</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $allItems = collect();
                        foreach($sales as $sale) {
                            if($sale->items) {
                                foreach($sale->items as $item) {
                                    $allItems->push($item);
                                }
                            }
                        }
                        $itemsByType = $allItems->groupBy('item_id');
                    @endphp
                    @forelse($itemsByType as $itemId => $items)
                        @php
                            $item = $items->first()->item;
                            $totalQuantity = $items->sum('quantity');
                            $totalAmount = $items->sum('amount');
                            $avgPrice = $totalQuantity > 0 ? $totalAmount / $totalQuantity : 0;
                        @endphp
                        <tr>
                            <td>{{ $item ? $item->name : 'غير محدد' }}</td>
                            <td class="text-center">{{ number_format($totalQuantity, 2) }}</td>
                            <td class="text-right amount">{{ number_format($totalAmount, 2) }} دينار ليبي</td>
                            <td class="text-right">{{ number_format($avgPrice, 2) }} دينار ليبي</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">لا توجد أصناف مباعة</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">توقيع مندوب المبيعات</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">توقيع المدير</div>
        </div>
    </div>
@endsection 