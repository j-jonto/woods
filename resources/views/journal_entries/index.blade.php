@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>القيود المحاسبية</h1>
        <a href="{{ route('journal_entries.create') }}" class="btn btn-primary">إنشاء قيد جديد</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>رقم المرجع</th>
                <th>الوصف</th>
                <th>الحالة</th>
                <th>مجموع المدين</th>
                <th>مجموع الدائن</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($entries as $entry)
                <tr>
                    <td>{{ $entry->entry_date->format('Y-m-d') }}</td>
                    <td>{{ $entry->reference_no }}</td>
                    <td>{{ Str::limit($entry->description, 50) }}</td>
                    <td><span class="badge bg-{{ $entry->status == 'posted' ? 'success' : ($entry->status == 'draft' ? 'warning' : 'danger') }}">
                        {{ $entry->status == 'posted' ? 'مرحل' : ($entry->status == 'draft' ? 'مسودة' : 'ملغي') }}
                    </span></td>
                    <td>${{ number_format($entry->getTotalDebit(), 2) }}</td>
                    <td>${{ number_format($entry->getTotalCredit(), 2) }}</td>
                    <td>
                        <a href="{{ route('journal_entries.show', $entry->id) }}" class="btn btn-sm btn-info">عرض</a>
                        @if ($entry->status == 'draft')
                            <a href="{{ route('journal_entries.edit', $entry->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                            <form action="{{ route('journal_entries.destroy', $entry->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                            @if ($entry->isBalanced())
                                <form action="{{ route('journal_entries.post', $entry->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل تريد ترحيل هذا القيد؟');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">ترحيل</button>
                                </form>
                            @else
                                <button type="button" class="btn btn-sm btn-secondary" disabled>غير متوازن</button>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $entries->links() }}
</div>
@endsection 