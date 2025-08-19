@extends('layouts.app')

@section('content')
<div class="container">
    <h1>تفاصيل القيد المحاسبي: {{ $journalEntry->reference_no }}</h1>

    <div class="card mb-4">
        <div class="card-header">
            معلومات القيد
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>تاريخ القيد:</strong> {{ $journalEntry->entry_date->format('Y-m-d') }}</p>
                    <p><strong>رقم المرجع:</strong> {{ $journalEntry->reference_no }}</p>
                    <p><strong>الحالة:</strong> <span class="badge bg-{{ $journalEntry->status == 'posted' ? 'success' : ($journalEntry->status == 'draft' ? 'warning' : 'danger') }}">{{ $journalEntry->status == 'posted' ? 'مرحل' : ($journalEntry->status == 'draft' ? 'مسودة' : 'ملغي') }}</span></p>
                </div>
                <div class="col-md-6">
                    <p><strong>الوصف:</strong> {{ $journalEntry->description ?? 'غير متوفر' }}</p>
                    <p><strong>أنشئ بواسطة:</strong> {{ $journalEntry->creator->name ?? 'النظام' }}</p>
                    <p><strong>آخر تحديث:</strong> {{ $journalEntry->updated_at->format('Y-m-d H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            بنود القيد
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>الحساب</th>
                        <th class="text-end">مدين</th>
                        <th class="text-end">دائن</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($journalEntry->items as $item)
                        <tr>
                            <td>{{ $item->account->name }} ({{ $item->account->code }})</td>
                            <td class="text-end">${{ number_format($item->debit, 2) }}</td>
                            <td class="text-end">${{ number_format($item->credit, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>المجموع</th>
                        <th class="text-end">${{ number_format($journalEntry->getTotalDebit(), 2) }}</th>
                        <th class="text-end">${{ number_format($journalEntry->getTotalCredit(), 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('journal_entries.index') }}" class="btn btn-secondary">العودة للقائمة</a>
        @if ($journalEntry->status == 'draft')
            <a href="{{ route('journal_entries.edit', $journalEntry->id) }}" class="btn btn-warning">تعديل القيد</a>
            @if ($journalEntry->isBalanced())
                <form action="{{ route('journal_entries.post', $journalEntry->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل تريد ترحيل هذا القيد؟');">
                    @csrf
                    <button type="submit" class="btn btn-success">ترحيل القيد</button>
                </form>
            @endif
        @endif
    </div>
</div>
@endsection 