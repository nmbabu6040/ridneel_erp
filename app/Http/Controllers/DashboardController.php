<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SrAttendance;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_users'  => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_roles'  => Role::count(),
        ];
        $charts = [];

        // Every widget (both the stat cards and the charts) only gets
        // computed — and only shown in the view — when the user
        // actually has access to that module, same permission-gating
        // pattern used everywhere else in this project.
        if ($user->hasPermission('inventory.view')) {
            $products = Product::where('is_active', true)->get();
            $stats['total_products']  = Product::count();
            $stats['low_stock_count'] = $products->filter->isLowStock()->count();

            $outOfStock = $products->filter(fn (Product $p) => $p->currentStock() <= 0)->count();
            $charts['stock_status'] = [
                'in_stock'  => $products->count() - $stats['low_stock_count'] - $outOfStock,
                'low_stock' => max(0, $stats['low_stock_count'] - $outOfStock),
                'out_stock' => $outOfStock,
            ];
        }

        if ($user->hasPermission('partners.view')) {
            $stats['total_dealers']   = Partner::type('dealer')->count();
            $stats['total_retailers'] = Partner::type('retailer')->count();
        }

        if ($user->hasPermission('sales.view')) {
            $todaySales = Sale::whereDate('sale_date', now()->toDateString())->where('status', 'confirmed');
            $stats['today_sales_count'] = (clone $todaySales)->count();
            $stats['today_sales_total'] = (clone $todaySales)->sum('total');
            $stats['total_due']         = Sale::where('status', 'confirmed')->sum('due_amount');

            $charts['sales_trend']  = $this->salesTrendData();
            $charts['top_products'] = $this->topProductsData();
        }

        if ($user->hasPermission('accounting.view')) {
            $stats['month_expenses'] = Expense::whereBetween('expense_date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount');
            $stats['total_payable']  = Purchase::where('status', 'confirmed')->sum('due_amount');

            $charts['expense_breakdown'] = Expense::whereBetween('expense_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->join('account_heads', 'account_heads.id', '=', 'expenses.account_head_id')
                ->selectRaw('account_heads.name as label, SUM(expenses.amount) as total')
                ->groupBy('account_heads.name')
                ->orderByDesc('total')
                ->limit(6)
                ->get();
        }

        if ($user->hasPermission('sr.tracking.view')) {
            $srUserIds = User::whereHas('role', fn ($q) => $q->where('slug', 'sr'))->pluck('id');
            $stats['total_sr']      = $srUserIds->count();
            $stats['sr_checked_in'] = SrAttendance::whereIn('user_id', $srUserIds)
                ->where('date', now()->toDateString())
                ->whereNotNull('check_in_at')
                ->count();
        }

        return view('dashboard.index', compact('user', 'stats', 'charts'));
    }

    /** Last 14 days of confirmed sales, split by Primary vs Secondary, for the trend line chart. */
    private function salesTrendData(): array
    {
        $start = now()->subDays(13)->startOfDay();

        $rows = Sale::where('status', 'confirmed')
            ->where('sale_date', '>=', $start)
            ->selectRaw('sale_date, type, SUM(total) as total')
            ->groupBy('sale_date', 'type')
            ->get()
            ->groupBy(fn ($r) => $r->sale_date->format('Y-m-d'));

        $labels = $primary = $secondary = [];
        for ($i = 0; $i < 14; $i++) {
            $date = now()->subDays(13 - $i);
            $key  = $date->format('Y-m-d');
            $labels[]    = $date->format('d M');
            $dayRows     = $rows->get($key, collect());
            $primary[]   = (float) $dayRows->firstWhere('type', 'primary')?->total;
            $secondary[] = (float) $dayRows->firstWhere('type', 'secondary')?->total;
        }

        return ['labels' => $labels, 'primary' => $primary, 'secondary' => $secondary];
    }

    /** Top 5 products by quantity sold this month, across confirmed sales. */
    private function topProductsData(): array
    {
        $rows = SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.status', 'confirmed')
            ->whereBetween('sales.sale_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->selectRaw('products.name as label, SUM(sale_items.quantity) as qty')
            ->groupBy('products.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        return [
            'labels' => $rows->pluck('label')->all(),
            'data'   => $rows->pluck('qty')->map(fn ($q) => (int) $q)->all(),
        ];
    }
}
