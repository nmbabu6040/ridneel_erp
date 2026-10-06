<?php

namespace App\Console\Commands;

use App\Models\Partner;
use App\Services\NotificationDispatcher;
use Illuminate\Console\Command;

class SendOverdueReminders extends Command
{
    protected $signature = 'app:send-overdue-reminders {--threshold=1 : Minimum outstanding balance to include}';
    protected $description = 'Email a list of dealers/retailers with an outstanding balance (run daily via scheduler)';

    /**
     * Simplification note: this flags any partner whose CURRENT balance
     * is positive (they owe us) above the threshold — it does not do
     * true invoice-aging (e.g. "overdue by 30+ days since that specific
     * invoice's due date"), since Sale doesn't carry a payment-due-date
     * field. Good enough as a daily "who still owes us money" nudge;
     * a proper aging report is a reasonable future enhancement.
     */
    public function handle(): int
    {
        $threshold = (float) $this->option('threshold');

        $partners = Partner::whereIn('type', ['dealer', 'retailer'])
            ->where('is_active', true)
            ->get()
            ->map(fn (Partner $p) => ['p' => $p, 'balance' => $p->currentBalance()])
            ->filter(fn ($row) => $row['balance'] > $threshold)
            ->sortByDesc('balance')
            ->values();

        if ($partners->isEmpty()) {
            $this->info('No overdue partners above threshold — nothing sent.');
            return self::SUCCESS;
        }

        NotificationDispatcher::send(
            eventKey: 'payment_overdue',
            subject: __('app.email_overdue_subject'),
            view: 'emails.payment-overdue',
            data: [
                'partners' => $partners->map(fn ($row) => [
                    'name'    => $row['p']->name,
                    'type'    => $row['p']->typeLabel(),
                    'balance' => number_format($row['balance'], 2),
                ])->all(),
            ],
            defaultRecipients: NotificationDispatcher::emailsWithPermission('accounting.view')
        );

        $this->info("Overdue reminder dispatched for {$partners->count()} partner(s).");

        return self::SUCCESS;
    }
}
