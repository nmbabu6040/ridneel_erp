<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountHead;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['accountHead', 'createdBy'])->latest('expense_date')->latest('id');

        if ($headId = $request->get('account_head_id')) {
            $query->where('account_head_id', $headId);
        }
        if ($from = $request->get('from')) {
            $query->whereDate('expense_date', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('expense_date', '<=', $to);
        }

        $expenses = $query->paginate(20)->withQueryString();
        $heads    = AccountHead::where('type', 'expense')->where('is_active', true)->orderBy('name')->get();
        $total    = (clone $query)->sum('amount');

        return view('accounting.expenses.index', compact('expenses', 'heads', 'total'));
    }

    public function create()
    {
        return view('accounting.expenses.form', [
            'expense' => new Expense(),
            'heads'   => AccountHead::where('type', 'expense')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;

        Expense::create($data);

        return redirect()->route('accounting.expenses.index')->with('success', __('app.expense_created'));
    }

    public function edit(Expense $expense)
    {
        return view('accounting.expenses.form', [
            'expense' => $expense,
            'heads'   => AccountHead::where('type', 'expense')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $expense->update($this->validated($request));

        return redirect()->route('accounting.expenses.index')->with('success', __('app.expense_updated'));
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('accounting.expenses.index')->with('success', __('app.expense_deleted'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'account_head_id' => ['required', 'exists:account_heads,id'],
            'expense_date'    => ['required', 'date'],
            'amount'          => ['required', 'numeric', 'min:0.01'],
            'paid_to'         => ['nullable', 'string', 'max:150'],
            'note'            => ['nullable', 'string', 'max:255'],
        ]);
    }
}
