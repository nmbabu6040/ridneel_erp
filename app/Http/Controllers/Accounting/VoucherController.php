<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    /**
     * A standalone balance movement not tied to a specific Sale/Purchase
     * — e.g. collecting overdue cash from a dealer, or paying a
     * supplier outside of any one invoice. Reuses the same
     * partner_transactions ledger Phase 3/5 already write to, so it
     * shows up correctly in the partner's statement (Ledger) alongside
     * invoice-linked entries.
     */
    public function create()
    {
        return view('accounting.vouchers.form', [
            'partners' => Partner::where('is_active', true)->orderBy('type')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'partner_id' => ['required', 'exists:partners,id'],
            'direction'  => ['required', Rule::in(['receipt', 'payment'])],
            'amount'     => ['required', 'numeric', 'min:0.01'],
            'note'       => ['nullable', 'string', 'max:255'],
        ]);

        // Receipt = money coming IN to us (partner pays us) → reduces
        // what they owe us → negative. Payment = money going OUT from
        // us (we pay the partner) → increases what they "owe us" back
        // toward zero/positive → positive. Same sign convention as
        // Sale::store() / Purchase::store() — see Partner::currentBalance().
        $signedAmount = $data['direction'] === 'receipt' ? -$data['amount'] : $data['amount'];

        PartnerTransaction::create([
            'partner_id' => $data['partner_id'],
            'type'       => 'payment',
            'amount'     => $signedAmount,
            'note'       => $data['note'] ?? ($data['direction'] === 'receipt' ? __('app.manual_receipt') : __('app.manual_payment')),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('accounting.ledger.show', $data['partner_id'])
            ->with('success', __('app.voucher_saved'));
    }
}
