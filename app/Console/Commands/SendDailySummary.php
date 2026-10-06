<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Models\Purchase;
use App\Models\ReturnTransaction;
use App\Models\Sale;
use App\Models\SrAttendance;
use App\Models\User;
use App\Services\NotificationDispatcher;
use Illuminate\Console\Command;

class SendDailySummary extends Command
{
    protected $signature = 'app:send-daily-summary';
    protected $description = "Email today's business summary to admins/managers (run once daily via scheduler, e.g. at 11:50pm)";

    public function handle(): int
    {
        $today = now()->toDateString();

        $sales = Sale::where('status', 'confirmed')->whereDate('sale_date', $today);
        $salesCount = (clone $sales)->count();
        $salesTotal = (clone $sales)->sum('total');

        $purchases = Purchase::where('status', 'confirmed')->whereDate('purchase_date', $today);
        $purchaseCount = (clone $purchases)->count();
        $purchaseTotal = (clone $purchases)->sum('total');

        $returnCount = ReturnTransaction::where('status', 'confirmed')->whereDate('return_date', $today)->count();

        $srUserIds = User::whereHas('role', fn ($q) => $q->where('slug', 'sr'))->pluck('id');
        $srTotal = $srUserIds->count();
        $srCheckedIn = SrAttendance::whereIn('user_id', $srUserIds)->where('date', $today)->whereNotNull('check_in_at')->count();

        $expenseTotal = Expense::whereDate('expense_date', $today)->sum('amount');
        $totalReceivable = Sale::where('status', 'confirmed')->sum('due_amount');

        NotificationDispatcher::send(
            eventKey: 'daily_summary',
            subject: __('app.email_daily_summary_subject') . ' — ' . now()->format('d M Y'),
            view: 'emails.daily-summary',
            data: [
                'date'            => now()->format('d M Y'),
                'salesCount'      => $salesCount,
                'salesTotal'      => number_format($salesTotal, 2),
                'purchaseCount'   => $purchaseCount,
                'purchaseTotal'   => number_format($purchaseTotal, 2),
                'returnCount'     => $returnCount,
                'srCheckedIn'     => $srCheckedIn,
                'srTotal'         => $srTotal,
                'expenseTotal'    => number_format($expenseTotal, 2),
                'totalReceivable' => number_format($totalReceivable, 2),
                'url'             => route('dashboard'),
            ],
            defaultRecipients: NotificationDispatcher::emailsWithPermission('accounting.view')
        );

        $this->info('Daily summary dispatched.');

        return self::SUCCESS;
    }
}
