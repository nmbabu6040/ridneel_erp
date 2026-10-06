<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Simplified Profit & Loss for a date range:
     *   Revenue  = SUM(sales.total) for confirmed sales in range
     *   COGS     = SUM(sale_items.quantity × product.purchase_price)
     *              — uses the product's CURRENT purchase price, not a
     *              historical per-lot cost (this system doesn't do
     *              FIFO/lot costing), so COGS here is an approximation.
     *              Good enough for a running P&L sense-check; swap in
     *              real landed-cost tracking later if precise margins
     *              are needed.
     *   Expenses = SUM(expenses.amount) in range
     *   Net      = (Revenue − COGS) − Expenses
     */
    public function profitLoss(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());
        $type = $request->get('type', 'all'); // all | primary | secondary

        $salesQuery = Sale::where('status', 'confirmed')->whereBetween('sale_date', [$from, $to]);
        if (in_array($type, ['primary', 'secondary'], true)) {
            $salesQuery->where('type', $type);
        }

        $revenue = (clone $salesQuery)->sum('total');
        $saleIds = (clone $salesQuery)->pluck('id');

        $cogs = SaleItem::whereIn('sale_id', $saleIds)
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->selectRaw('SUM(sale_items.quantity * products.purchase_price) as total_cost')
            ->value('total_cost') ?? 0;

        $expenses = Expense::whereBetween('expense_date', [$from, $to])->sum('amount');

        $grossProfit = $revenue - $cogs;
        $netProfit   = $grossProfit - $expenses;

        $expenseBreakdown = Expense::whereBetween('expense_date', [$from, $to])
            ->selectRaw('account_head_id, SUM(amount) as total')
            ->groupBy('account_head_id')
            ->with('accountHead')
            ->get();

        return view('accounting.reports.profit-loss', compact(
            'from', 'to', 'type', 'revenue', 'cogs', 'grossProfit', 'expenses', 'netProfit', 'expenseBreakdown'
        ));
    }
}
