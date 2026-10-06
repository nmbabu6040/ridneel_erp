<?php

namespace Database\Seeders;

use App\Models\NotificationSetting;
use Illuminate\Database\Seeder;

class NotificationSettingSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            ['event_key' => 'user_welcome',    'label' => 'New user account created',       'label_bn' => 'নতুন ইউজার অ্যাকাউন্ট তৈরি হলে'],
            ['event_key' => 'low_stock',       'label' => 'Product stock reaches reorder level', 'label_bn' => 'পণ্যের স্টক রি-অর্ডার লেভেলে পৌঁছালে'],
            ['event_key' => 'new_invoice',     'label' => 'New Sales invoice created',      'label_bn' => 'নতুন সেলস ইনভয়েস তৈরি হলে'],
            ['event_key' => 'new_purchase',    'label' => 'New Purchase recorded',          'label_bn' => 'নতুন ক্রয় রেকর্ড হলে'],
            ['event_key' => 'new_return',      'label' => 'New Return recorded',            'label_bn' => 'নতুন রিটার্ন রেকর্ড হলে'],
            ['event_key' => 'sr_late_checkin', 'label' => 'SR checks in late',              'label_bn' => 'এসআর দেরিতে চেক-ইন করলে'],
            ['event_key' => 'target_achieved', 'label' => 'SR/Dealer reaches 100% of monthly target', 'label_bn' => 'এসআর/ডিলার মাসিক টার্গেটের ১০০% অর্জন করলে'],
            ['event_key' => 'payment_overdue', 'label' => 'Partner balance overdue reminder (daily)', 'label_bn' => 'পার্টনারের বকেয়া রিমাইন্ডার (প্রতিদিন)'],
            ['event_key' => 'daily_summary',   'label' => "Daily business summary (to admins/managers)", 'label_bn' => 'দৈনিক ব্যবসায়িক সারসংক্ষেপ (অ্যাডমিন/ম্যানেজারদের জন্য)'],
        ];

        foreach ($events as $event) {
            NotificationSetting::updateOrCreate(
                ['event_key' => $event['event_key']],
                $event + ['email_enabled' => true]
            );
        }
    }
}
