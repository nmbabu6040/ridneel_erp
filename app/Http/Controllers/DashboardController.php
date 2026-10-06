<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\Product;
use App\Models\Role;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
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

        // Inventory widgets only computed (and only shown in the view)
        // when the user actually has access to that module — Phase 2.
        if ($user->hasPermission('inventory.view')) {
            $stats['total_products'] = Product::count();
            $stats['low_stock_count'] = Product::where('is_active', true)
                ->get()
                ->filter->isLowStock()
                ->count();
        }
        if ($user->hasPermission('partners.view')) {
            $stats['total_dealers']   = Partner::type('dealer')->count();
            $stats['total_retailers'] = Partner::type('retailer')->count();
        }
        if ($user->hasPermission('sales.view')) {
            $todaySales = Sale::whereDate('sale_date', now()->toDateString())
                ->where('status', 'confirmed');
            $stats['today_sales_count'] = (clone $todaySales)->count();
            $stats['today_sales_total'] = (clone $todaySales)->sum('total');
            $stats['total_due']         = Sale::where('status', 'confirmed')->sum('due_amount');
        }
        if ($user->hasPermission('accounting.view')) {
            $stats['month_expenses']  = Expense::whereBetween('expense_date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount');
            $stats['total_payable']   = Purchase::where('status', 'confirmed')->sum('due_amount');
        }
        if ($user->hasPermission('sr.tracking.view')) {
            $srUserIds = User::whereHas('role', fn ($q) => $q->where('slug', 'sr'))->pluck('id');
            $stats['total_sr']       = $srUserIds->count();
            $stats['sr_checked_in']  = SrAttendance::whereIn('user_id', $srUserIds)
                ->where('date', now()->toDateString())
                ->whereNotNull('check_in_at')
                ->count();
        }

        return view('dashboard.index', compact('user', 'stats'));
    }
}
