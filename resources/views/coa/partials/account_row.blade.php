<tr style="padding-left: {{ $level * 20 }}px;">
    <td>{{ $account->code }}</td>
    <td>{{ str_repeat('--', $level) }} {{ $account->name }}</td>
    <td>{{ ucfirst($account->type) }}</td>
    <td>{{ $account->level }}</td>
    <td>{{ $account->parent->name ?? 'N/A' }}</td>
    <td>
        <span class="badge bg-{{ $account->is_active ? 'success' : 'danger' }}">
            {{ $account->is_active ? 'Active' : 'Inactive' }}
        </span>
    </td>
    <td>
        <a href="{{ route('coa.edit', $account->id) }}" class="btn btn-sm btn-warning">Edit</a>
        <form action="{{ route('coa.destroy', $account->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this account? This will also delete all sub-accounts.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
        </form>
    </td>
</tr>
@if ($account->children->count() > 0)
    @foreach ($account->children as $child)
        @include('coa.partials.account_row', ['account' => $child, 'level' => $level + 1])
    @endforeach
@endif 