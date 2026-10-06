<?php

namespace Database\Seeders;

use App\Models\AccountHead;
use Illuminate\Database\Seeder;

class AccountHeadSeeder extends Seeder
{
    public function run(): void
    {
        $heads = [
            ['code' => '5001', 'type' => 'expense', 'name' => 'Office Rent', 'name_bn' => 'অফিস ভাড়া'],
            ['code' => '5002', 'type' => 'expense', 'name' => 'Staff Salary', 'name_bn' => 'কর্মচারী বেতন'],
            ['code' => '5003', 'type' => 'expense', 'name' => 'Fuel & Transport', 'name_bn' => 'জ্বালানি ও পরিবহন'],
            ['code' => '5004', 'type' => 'expense', 'name' => 'Utility Bills', 'name_bn' => 'ইউটিলিটি বিল'],
            ['code' => '5005', 'type' => 'expense', 'name' => 'Office Supplies', 'name_bn' => 'অফিস সরঞ্জাম'],
            ['code' => '5006', 'type' => 'expense', 'name' => 'Marketing & Promotion', 'name_bn' => 'বিপণন ও প্রচার'],
            ['code' => '5007', 'type' => 'expense', 'name' => 'Repair & Maintenance', 'name_bn' => 'মেরামত ও রক্ষণাবেক্ষণ'],
            ['code' => '5099', 'type' => 'expense', 'name' => 'Miscellaneous', 'name_bn' => 'বিবিধ'],
            ['code' => '1001', 'type' => 'asset', 'name' => 'Cash in Hand', 'name_bn' => 'হাতে নগদ'],
            ['code' => '1002', 'type' => 'asset', 'name' => 'Bank Account', 'name_bn' => 'ব্যাংক হিসাব'],
            ['code' => '1003', 'type' => 'asset', 'name' => 'Inventory', 'name_bn' => 'মজুদ পণ্য'],
            ['code' => '2001', 'type' => 'liability', 'name' => 'Accounts Payable', 'name_bn' => 'প্রদেয় হিসাব'],
            ['code' => '4001', 'type' => 'income', 'name' => 'Sales Revenue', 'name_bn' => 'বিক্রয় আয়'],
            ['code' => '3001', 'type' => 'equity', 'name' => "Owner's Capital", 'name_bn' => 'মালিকের মূলধন'],
        ];

        foreach ($heads as $head) {
            AccountHead::updateOrCreate(['code' => $head['code']], $head);
        }
    }
}
