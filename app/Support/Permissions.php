<?php

namespace App\Support;

/**
 * Single source of truth for every permission key in the system.
 * Organised by module so future phases (Sales, Inventory, SR Tracking,
 * Accounting, KPI...) simply add their block here — nothing else needs
 * to change for a new permission to become assignable from the Roles UI.
 */
class Permissions
{
    public static function all(): array
    {
        return [
            'Dashboard' => [
                'dashboard.view' => ['en' => 'View Dashboard', 'bn' => 'ড্যাশবোর্ড দেখা'],
            ],
            'User Management' => [
                'users.view'   => ['en' => 'View Users', 'bn' => 'ইউজার দেখা'],
                'users.create' => ['en' => 'Create Users', 'bn' => 'ইউজার তৈরি করা'],
                'users.edit'   => ['en' => 'Edit Users', 'bn' => 'ইউজার সম্পাদনা'],
                'users.delete' => ['en' => 'Delete Users', 'bn' => 'ইউজার মুছে ফেলা'],
            ],
            'Role Management' => [
                'roles.view'   => ['en' => 'View Roles', 'bn' => 'রোল দেখা'],
                'roles.create' => ['en' => 'Create Roles', 'bn' => 'রোল তৈরি করা'],
                'roles.edit'   => ['en' => 'Edit Roles', 'bn' => 'রোল সম্পাদনা'],
                'roles.delete' => ['en' => 'Delete Roles', 'bn' => 'রোল মুছে ফেলা'],
            ],
            // ---- Phase 2: live now ----
            'Inventory' => [
                'inventory.view'   => ['en' => 'View Products / Categories / Units / Warehouses', 'bn' => 'পণ্য/ক্যাটাগরি/ইউনিট/গুদাম দেখা'],
                'inventory.manage' => ['en' => 'Manage Products / Categories / Units / Warehouses', 'bn' => 'পণ্য/ক্যাটাগরি/ইউনিট/গুদাম ব্যবস্থাপনা'],
            ],
            'Partners (Dealer/Retailer/Mechanic)' => [
                'partners.view'   => ['en' => 'View Dealers / Retailers / Mechanics', 'bn' => 'ডিলার/রিটেইলার/মেকানিক দেখা'],
                'partners.manage' => ['en' => 'Manage Dealers / Retailers / Mechanics', 'bn' => 'ডিলার/রিটেইলার/মেকানিক ব্যবস্থাপনা'],
            ],
            'Stock' => [
                'stock.view'   => ['en' => 'View Stock Ledger', 'bn' => 'স্টক লেজার দেখা'],
                'stock.adjust' => ['en' => 'Opening Stock / Adjustment Entry', 'bn' => 'ওপেনিং স্টক / অ্যাডজাস্টমেন্ট এন্ট্রি'],
            ],
            // ---- Phase 3: live now ----
            'Sales' => [
                'sales.view'      => ['en' => 'View Sales / Invoices', 'bn' => 'সেলস/ইনভয়েস দেখা'],
                'sales.primary'   => ['en' => 'Primary Sales (Company → Dealer)', 'bn' => 'প্রাইমারি সেলস (কোম্পানি → ডিলার)'],
                'sales.secondary' => ['en' => 'Secondary Sales (Dealer → Retailer)', 'bn' => 'সেকেন্ডারি সেলস (ডিলার → রিটেইলার)'],
            ],
            // ---- Phase 4: live now ----
            'SR Tracking & Attendance' => [
                'sr.attendance'    => ['en' => 'Face Attendance Check-in/out (own)', 'bn' => 'ফেস অ্যাটেন্ডেন্স চেক-ইন/আউট (নিজের)'],
                'sr.tracking.view' => ['en' => 'View SR Live Map & Attendance Reports', 'bn' => 'এসআর লাইভ ম্যাপ ও হাজিরা রিপোর্ট দেখা'],
            ],
            // ---- Phase 5: live now ----
            'Accounting' => [
                'accounting.view'   => ['en' => 'View Purchases / Expenses / Ledger / Reports', 'bn' => 'ক্রয়/খরচ/লেজার/রিপোর্ট দেখা'],
                'accounting.manage' => ['en' => 'Manage Purchases / Expenses / Accounts / Vouchers', 'bn' => 'ক্রয়/খরচ/হিসাব/ভাউচার ব্যবস্থাপনা'],
            ],
            // ---- Phase 6: live now ----
            'Returns & Exchange' => [
                'returns.view'   => ['en' => 'View Returns', 'bn' => 'রিটার্ন দেখা'],
                'returns.manage' => ['en' => 'Create / Cancel Returns', 'bn' => 'রিটার্ন তৈরি/বাতিল করা'],
            ],
            // ---- Phase 7: live now ----
            // ---- Phase 8: live now ----
            'System Settings' => [
                'settings.manage' => ['en' => 'Manage Notification & System Settings', 'bn' => 'নোটিফিকেশন ও সিস্টেম সেটিংস ব্যবস্থাপনা'],
            ],
            'KPI & Commission' => [
                'kpi.view'   => ['en' => 'View KPI, Commission & Leaderboard', 'bn' => 'কেপিআই, কমিশন ও লিডারবোর্ড দেখা'],
                'kpi.manage' => ['en' => 'Set Targets & Commission Rates', 'bn' => 'টার্গেট ও কমিশন রেট নির্ধারণ'],
            ],
        ];
    }

    /** Flat list of every key, e.g. for validation. */
    public static function keys(): array
    {
        $keys = [];
        foreach (self::all() as $group) {
            $keys = array_merge($keys, array_keys($group));
        }
        return $keys;
    }
}
