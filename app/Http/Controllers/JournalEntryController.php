<?php
namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index()
    {
        $entries = JournalEntry::with('items')->orderByDesc('entry_date')->paginate(20);
        return view('journal_entries.index', compact('entries'));
    }

    public function create()
    {
        $accounts = ChartOfAccount::active()->get();
        return view('journal_entries.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'reference_no' => 'required|unique:journal_entries',
            'description' => 'nullable',
            'items' => 'required|array|min:2',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id',
            'items.*.debit' => 'required_without:items.*.credit|numeric',
            'items.*.credit' => 'required_without:items.*.debit|numeric',
        ]);
        DB::transaction(function () use ($validated, $request) {
            $entry = JournalEntry::create([
                'entry_date' => $validated['entry_date'],
                'reference_no' => $validated['reference_no'],
                'description' => $validated['description'] ?? null,
                'status' => 'draft',
                'created_by' => $request->user()->id,
            ]);
            foreach ($validated['items'] as $item) {
                JournalEntryItem::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $item['account_id'],
                    'debit' => $item['debit'] ?? 0,
                    'credit' => $item['credit'] ?? 0,
                    'description' => $item['description'] ?? null,
                ]);
            }
        });
        return redirect()->route('journal_entries.index')->with('success', 'Journal entry created.');
    }

    public function show(JournalEntry $journalEntry)
    {
        $journalEntry->load('items.account');
        return view('journal_entries.show', compact('journalEntry'));
    }

    public function edit(JournalEntry $journalEntry)
    {
        $accounts = ChartOfAccount::active()->get();
        $journalEntry->load('items');
        return view('journal_entries.edit', compact('journalEntry', 'accounts'));
    }

    public function update(Request $request, JournalEntry $journalEntry)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'reference_no' => 'required|unique:journal_entries,reference_no,' . $journalEntry->id,
            'description' => 'nullable',
            'items' => 'required|array|min:2',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id',
            'items.*.debit' => 'required_without:items.*.credit|numeric',
            'items.*.credit' => 'required_without:items.*.debit|numeric',
        ]);
        DB::transaction(function () use ($validated, $journalEntry) {
            $journalEntry->update([
                'entry_date' => $validated['entry_date'],
                'reference_no' => $validated['reference_no'],
                'description' => $validated['description'] ?? null,
            ]);
            $journalEntry->items()->delete();
            foreach ($validated['items'] as $item) {
                JournalEntryItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $item['account_id'],
                    'debit' => $item['debit'] ?? 0,
                    'credit' => $item['credit'] ?? 0,
                    'description' => $item['description'] ?? null,
                ]);
            }
        });
        return redirect()->route('journal_entries.index')->with('success', 'Journal entry updated.');
    }

    public function destroy(JournalEntry $journalEntry)
    {
        $journalEntry->delete();
        return redirect()->route('journal_entries.index')->with('success', 'Journal entry deleted.');
    }

    public function post(JournalEntry $journalEntry)
    {
        try {
            $journalEntry->post();
            return redirect()->route('journal_entries.index')->with('success', 'Journal entry posted.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
} 