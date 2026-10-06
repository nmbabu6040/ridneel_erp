<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountHead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountHeadController extends Controller
{
    public function index()
    {
        $accountHeads = AccountHead::withCount('expenses')->orderBy('type')->orderBy('name')->paginate(20);

        return view('accounting.account-heads.index', compact('accountHeads'));
    }

    public function create()
    {
        return view('accounting.account-heads.form', ['accountHead' => new AccountHead()]);
    }

    public function store(Request $request): RedirectResponse
    {
        AccountHead::create($this->validated($request));

        return redirect()->route('accounting.account-heads.index')->with('success', __('app.account_head_created'));
    }

    public function edit(AccountHead $accountHead)
    {
        return view('accounting.account-heads.form', compact('accountHead'));
    }

    public function update(Request $request, AccountHead $accountHead): RedirectResponse
    {
        $accountHead->update($this->validated($request, $accountHead->id));

        return redirect()->route('accounting.account-heads.index')->with('success', __('app.account_head_updated'));
    }

    public function destroy(AccountHead $accountHead): RedirectResponse
    {
        if ($accountHead->expenses()->exists()) {
            return back()->with('error', __('app.account_head_in_use'));
        }

        $accountHead->delete();

        return redirect()->route('accounting.account-heads.index')->with('success', __('app.account_head_deleted'));
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'name_bn'   => ['nullable', 'string', 'max:150'],
            'code'      => ['required', 'string', 'max:20', Rule::unique('account_heads', 'code')->ignore($ignoreId)],
            'type'      => ['required', Rule::in(['asset', 'liability', 'income', 'expense', 'equity'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
