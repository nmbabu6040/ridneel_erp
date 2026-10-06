<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    /** Picker screen — choose which partner's statement to view. */
    public function index(Request $request)
    {
        $type = $request->get('type', 'dealer');

        $partners = Partner::type($type)->orderBy('name')->get();

        return view('accounting.ledger.index', compact('partners', 'type'));
    }

    /**
     * Full running-balance statement for one partner: opening balance,
     * then every partner_transactions row in date order with a
     * cumulative running total — the same non-drift ledger principle
     * used throughout this project (stock_movements, partner_transactions
     * themselves).
     */
    public function show(Partner $partner)
    {
        $transactions = $partner->transactions()->orderBy('created_at')->orderBy('id')->get();

        $running = (float) $partner->opening_balance;
        $rows = $transactions->map(function ($tx) use (&$running) {
            $running += (float) $tx->amount;
            $tx->running_balance = $running;
            return $tx;
        })->reverse()->values(); // newest first for display

        return view('accounting.ledger.show', [
            'partner'      => $partner,
            'rows'         => $rows,
            'openingBalance' => $partner->opening_balance,
            'currentBalance' => $running,
        ]);
    }
}
