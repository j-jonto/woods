@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">حسابات الصندوق والبنك</h4>
    <a href="{{ route('cash_accounts.create') }}" class="btn btn-success mb-3">إضافة حساب جديد</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>الرقم</th>
                <th>الاسم</th>
                <th>النوع</th>
                <th>الرصيد</th>
                <th>الحالة</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accounts as $account)
            <tr>
                <td>{{ $account->id }}</td>
                <td>{{ $account->name }}</td>
                <td>{{ $account->type == 'cash' ? 'صندوق' : 'بنك' }}</td>
                                                                <td>{{ number_format($account->balance, 2) }} دينار ليبي</td>
                <td>{{ $account->is_active ? 'نشط' : 'غير نشط' }}</td>
                <td>
                    <a href="{{ route('cash_accounts.show', $account->id) }}" class="btn btn-info btn-sm">عرض</a>
                    <a href="{{ route('cash_accounts.edit', $account->id) }}" class="btn btn-warning btn-sm">تعديل</a>
                    <form action="{{ route('cash_accounts.destroy', $account->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $accounts->links() }}
</div>
@endsection 