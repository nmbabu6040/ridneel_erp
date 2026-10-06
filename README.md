# Ridneel DMS ERP — Phase 1 থেকে 8 (সম্পূর্ণ রোডম্যাপ + Email Notifications)

**স্ট্যাটাস:** ✅ Phase 1 (Foundation), Phase 2 (Master Data & Inventory), Phase 3
(Primary & Secondary Sales), Phase 4 (SR GPS Live Tracking & Face Attendance), Phase 5
(Accounting Integration), Phase 6 (Return/Exchange Management), Phase 7 (KPI,
Commission & Reward), Phase 8 (Email Notification System) — **সবগুলো ধাপ সম্পূর্ণ ও
কার্যকর।** শুরুতে প্রতিশ্রুত পুরো রোডম্যাপ + ইমেইল নোটিফিকেশন এখন একসাথে কাজ করে।
**প্রযুক্তি:** Laravel (10.x অথবা 11.x/12.x) + Bootstrap 5.3 + jQuery + Vanilla JS + Blade

এই প্যাকেজে **শুধু অ্যাপ্লিকেশন কোড** আছে (controllers, models, migrations, views, routes,
language files) — Laravel framework-এর core ফাইল (vendor, composer.json ইত্যাদি) এখানে
নেই, কারণ এটি আপনার নিজের সার্ভারে standard `composer create-project` দিয়ে বসাতে হবে।
এভাবে দেওয়ার কারণ: আপনি PHP/Composer version, database driver ইত্যাদি নিজের সার্ভার
অনুযায়ী ঠিক রাখতে পারবেন, আর framework আপডেট থাকবে।

---

## Phase 1-এ যা যা আছে (সব real, working — কোনো dummy code নেই)

- ✅ পূর্ণাঙ্গ Login system (session-based, remember me, account-suspend check)
- ✅ Role & Permission system — role তৈরি/সম্পাদনা, প্রতিটি role-এ granular permission
  (checkbox list) নির্ধারণ, Super Admin auto full-access
- ✅ User Management — CRUD, role assign, status (active/inactive/suspended), search
- ✅ Middleware-based route protection (`permission:users.view` টাইপের)
- ✅ Blade `@can()` / `@cannot()` পুরো সিস্টেমে কাজ করে (Gate::before hook দিয়ে)
- ✅ দ্বিভাষা (বাংলা ⇄ English) — এক ক্লিকে সুইচ, প্রতি ইউজারের জন্য আলাদা মনে রাখে
- ✅ Dark mode — টগল বাটন, localStorage-এ সংরক্ষিত থাকে, flash হয় না
- ✅ Fully responsive — mobile-এ sidebar collapse হয়ে হ্যামবার্গার মেনু হয়ে যায়
- ✅ Dashboard — real user/role count দেখায় (কোনো fake number নেই), এবং প্রজেক্ট রোডম্যাপ
- ✅ পরবর্তী Phase-গুলোর জন্য permission key ও sidebar placeholder আগে থেকেই তৈরি —
  যাতে Phase 2/3/4... যোগ করার সময় Roles UI বা navigation নতুন করে ভাঙতে না হয়

**কোনো composer package লাগবে না** (Spatie/Permission ইত্যাদি ব্যবহার করা হয়নি) —
role-এর permission একটি সাধারণ JSON column-এ রাখা হয়েছে, যা simple, fast এবং
maintain করা সহজ।

---

## Phase 2-এ যা যা আছে (Master Data & Inventory)

- ✅ **Category** master (CRUD, বাংলা/English নাম, active/inactive)
- ✅ **Unit** master (Piece/Kg/Liter ইত্যাদি, short code সহ)
- ✅ **Warehouse** master (একাধিক গুদাম, একটাকে ডিফল্ট সেট করা যায়)
- ✅ **Product** master — category+unit link, SKU/barcode (unique), purchase/sale price,
  reorder level সহ **live stock quantity** (প্রতিটি প্রোডাক্টের পাশে দেখা যায়)
- ✅ **Dealer / Retailer / Mechanic** — একই "Partner" মডেলে তিন ধরনের partner, ট্যাব দিয়ে
  আলাদা করা, Retailer একটি নির্দিষ্ট Dealer-এর সাথে লিংক করা যায়, opening balance ও
  credit limit ফিল্ডসহ (Phase 5 Accounting এখানেই hook হবে)
- ✅ **Stock Ledger** — append-only movement log (Opening/Purchase/Sales/Return/
  Adjustment/Transfer টাইপ সহ প্রস্তুত, যদিও Phase 2-তে শুধু Opening ও Adjustment এন্ট্রি
  ম্যানুয়ালি করা যায় — বাকিগুলো Phase 3/5/6 থেকে auto-generate হবে)
- ✅ **Low-stock alert** — Dashboard-এ কাউন্ট, Product list-এ ফিল্টার ও red badge

**গুরুত্বপূর্ণ ডিজাইন সিদ্ধান্ত:** কোনো প্রোডাক্টের "current stock" একটি আলাদা কলামে রাখা হয়নি —
বরং প্রতিবার `stock_movements` টেবিলের SUM() নেওয়া হয়। এতে stock কখনো real entry-র
সাথে out-of-sync হতে পারে না। ডেটা অনেক বড় হয়ে গেলে ভবিষ্যতে একটি cached summary
টেবিল যোগ করা যাবে (স্কিমা ভাঙবে না)।

---

## Phase 3-এ যা যা আছে (Primary & Secondary Sales)

- ✅ **Primary Sales** (Company → Dealer) ও **Secondary Sales** (Dealer → Retailer) — একই
  ইঞ্জিন, আলাদা ট্যাব, আলাদা permission (`sales.primary` / `sales.secondary`)
- ✅ ইনভয়েস তৈরি — dynamic multi-item form (product যোগ/বাদ দেওয়া যায় JS দিয়ে, কোনো
  পেজ রিলোড ছাড়াই), unit price product থেকে auto-fill হয়, real-time subtotal/total/due
  calculation
- ✅ **Stock validation** — সাবমিট করার আগেই চেক হয় নির্বাচিত গুদামে পর্যাপ্ত স্টক আছে কিনা;
  না থাকলে পুরো ইনভয়েস reject হবে (partial invoice কখনো তৈরি হবে না)
- ✅ Invoice কনফার্ম হওয়ার সাথে সাথে automatically:
  - স্টক লেজারে `sales_out` এন্ট্রি হয় (Phase 2-এর ledger ব্যবহার করেই — নতুন কোনো
    স্টক-লজিক তৈরি করা হয়নি)
  - Dealer/Retailer-এর ব্যালেন্সে (`partner_transactions`) sale + payment এন্ট্রি হয়
- ✅ **Cancel Invoice** — একটি বাটনে সম্পূর্ণ reversal: স্টক ফেরত (`adjustment_in`),
  ব্যালেন্স রিভার্সাল — সব একটি DB transaction-এর মধ্যে, তাই আংশিক reversal হওয়ার
  সুযোগ নেই
- ✅ Invoice **Print** view (ঝকঝকে A4-friendly layout, sidebar/topbar প্রিন্টে hide হয়ে যায়)
- ✅ Dashboard-এ আজকের ইনভয়েস সংখ্যা, আজকের বিক্রয় ও মোট বকেয়া (Total Due) widget

**জানা সীমাবদ্ধতা (পরে ঠিক করা যাবে প্রয়োজনে):** ইনভয়েস *দেখার* permission
(`sales.view`) টাইপ-নির্দিষ্ট নয় — অর্থাৎ যার `sales.view` আছে সে Primary ও Secondary
দুই ধরনের ইনভয়েসই আইডি দিয়ে দেখতে পারবে (তৈরি/বাতিল করার permission অবশ্যই
টাইপ-নির্দিষ্ট)। প্রয়োজন হলে `sales.primary.view` / `sales.secondary.view` আলাদা করে
দেওয়া যাবে।

---

## Phase 4-এ যা যা আছে (SR GPS Live Tracking & Face Attendance)

- ✅ **Face Enrollment** — SR একবার ক্যামেরায় নিজের মুখ ক্যাপচার করে "enroll" করে; ব্রাউজারে
  ([face-api.js](https://github.com/justadudewhohacks/face-api.js)) দিয়ে ১২৮-সংখ্যার face
  descriptor বের করে ডাটাবেজে সংরক্ষণ হয়
- ✅ **Face Attendance Check-in/out** — প্রতিদিন ক্যামেরায় লাইভ ক্যাপচার নিয়ে এনরোল করা ফেসের
  সাথে মিলিয়ে (euclidean distance ≤ 0.6) দেখা হয়, মিললে তবেই check-in/out সম্পন্ন হয় — GPS
  লোকেশনসহ
- ✅ **Live GPS Tracking** — check-in করার পর থেকে check-out না করা পর্যন্ত প্রতি ৯০ সেকেন্ডে
  ব্রাউজার background-এ লোকেশন পাঠায়; Manager-এর জন্য **Live Map** (Leaflet + OpenStreetMap
  — কোনো Google Maps API key লাগে না) সব SR-এর সর্বশেষ অবস্থান দেখায়
- ✅ **Attendance Report** — Manager যেকোনো তারিখের জন্য সব SR-এর check-in/out সময়, worked
  hours, status ও লোকেশন লিংক ও থাম্বনেইল ছবি দেখতে পারেন
- ✅ Late detection — সকাল ১০টার পর check-in করলে status স্বয়ংক্রিয়ভাবে "Late" হয়ে যায়
  (থ্রেশহোল্ড কোডে এক জায়গায়, প্রয়োজনে বদলানো সহজ)

**⚠️ গুরুত্বপূর্ণ সীমাবদ্ধতা (সততার সাথে জানানো প্রয়োজন):**
1. **ফেস ম্যাচিং সম্পূর্ণ ব্রাউজারে (client-side JavaScript) হয়** — সার্ভারে কোনো PHP-based
   face-recognition নেই (এমন লাইব্রেরি এই পরিবেশে ইনস্টল করা সম্ভব ছিল না)। এর মানে,
   টেকনিক্যালি দক্ষ কেউ ব্রাউজারের DevTools দিয়ে ম্যাচ distance মান বদলে দিতে পারলে
   bypass করতে পারবে। সাধারণ ব্যবহারের জন্য এটি যথেষ্ট সুরক্ষিত, কিন্তু high-security
   প্রয়োজন হলে ভবিষ্যতে সার্ভার-সাইড ভেরিফিকেশন (যেমন একটি ছোট Python/AI মাইক্রোসার্ভিস)
   যোগ করার পরামর্শ থাকবে।
2. **face-api.js মডেল ফাইল** ইন্টারনেট থেকে (jsDelivr-এর GitHub mirror) লোড হয় — তাই
   SR/Manager-এর ডিভাইসে ইন্টারনেট কানেকশন থাকতে হবে যখন এই পেজ খোলা হবে।
3. এটি একটি **মোবাইল-রেসপনসিভ ওয়েব পেজ** (ব্রাউজার দিয়ে ব্যবহার করা যায়, ফোনের ক্যামেরা ও
   GPS অ্যাক্সেস করে) — সত্যিকারের নেটিভ মোবাইল অ্যাপ (Play Store/App Store) নয়। HTTPS
   ছাড়া ক্যামেরা/লোকেশন পারমিশন কাজ করবে না — **প্রোডাকশনে অবশ্যই SSL সার্টিফিকেট লাগবে।**

---

## Phase 5-এ যা যা আছে (Accounting Integration)

- ✅ **Supplier** — Partner মডেলের ৪র্থ টাইপ হিসেবে যোগ হয়েছে (Dealer/Retailer/Mechanic-এর
  মতোই, একই CRUD পেজ থেকে ট্যাব দিয়ে আলাদা — নতুন কোনো আলাদা মডিউল বানাতে হয়নি)
- ✅ **Purchase** — সাপ্লায়ার থেকে ক্রয়, Sales-এর মতোই dynamic multi-item ফর্ম; কনফার্ম
  হলে স্টক লেজারে `purchase_in` এন্ট্রি ও সাপ্লায়ারের ব্যালেন্সে স্বয়ংক্রিয় আপডেট, এবং
  এক ক্লিকে সম্পূর্ণ reversal-সহ **Cancel**
- ✅ **Chart of Accounts** — হালকা-পাতলা কিন্তু বাস্তব অ্যাকাউন্ট হেড master (Asset/
  Liability/Income/Expense/Equity টাইপসহ), ডিফল্ট কিছু হেড আগে থেকেই সিড করা আছে
- ✅ **Expense Entry** — অ্যাকাউন্ট হেড বেছে খরচ রেকর্ড, তারিখ/হেড দিয়ে ফিল্টার ও টোটাল
- ✅ **Payment/Receipt Voucher** — যেকোনো Dealer/Retailer/Mechanic/Supplier-এর সাথে
  ইনভয়েসের বাইরে সরাসরি টাকা লেনদেন রেকর্ড করার ফর্ম (Receipt = টাকা এসেছে, Payment =
  টাকা গেছে) — Phase 3-এর `partner_transactions` লেজারেই যোগ হয়, তাই Statement-এ ইনভয়েস
  ও ভাউচার সব একসাথে ক্রমানুসারে দেখা যায়
- ✅ **Partner Ledger / Statement** — প্রতিটি Dealer/Retailer/Mechanic/Supplier-এর
  opening balance + প্রতিটি লেনদেনের পর running balance দেখানো একটি পূর্ণাঙ্গ statement
  পেজ, তালিকায় current balance অনুযায়ী রঙ (লাল = ওরা পাওনা, হলুদ = আমরা পাওনা)
- ✅ **Profit & Loss রিপোর্ট** — তারিখ-পরিসর অনুযায়ী Revenue, COGS, Gross Profit,
  Expense breakdown ও Net Profit

**⚠️ সৎভাবে জানানো সীমাবদ্ধতাসমূহ:**
1. এটি **সম্পূর্ণ ডাবল-এন্ট্রি জেনারেল লেজার (GL) নয়** — Chart of Accounts এখনো শুধু
   খরচ ক্যাটাগরাইজেশনের জন্য ব্যবহৃত হচ্ছে, প্রতিটি Sale/Purchase/Payment-এর জন্য
   স্বয়ংক্রিয় journal posting তৈরি হয় না। বাস্তব ডেটা (stock ledger, partner ledger)
   থেকেই P&L হিসাব করা হয়, যা ছোট-মাঝারি ডিস্ট্রিবিউশন ব্যবসার জন্য কার্যকর, কিন্তু একটি
   ফুল অডিট-গ্রেড অ্যাকাউন্টিং সিস্টেম নয়।
2. Profit & Loss-এর **COGS আনুমানিক** — প্রতিটি পণ্যের *বর্তমান* purchase price দিয়ে
   হিসাব হয়, বিক্রির সময়কার প্রকৃত লট-cost দিয়ে নয় (কোনো FIFO/lot costing নেই)। রিপোর্ট
   পেজেই এই নোট স্পষ্টভাবে দেখানো আছে।
3. পুরনো কোনো Purchase বাতিল করার সময়, সেই স্টক ইতিমধ্যে অন্য কোথাও বিক্রি হয়ে গিয়ে
   থাকলে warehouse-এর স্টক ঋণাত্মক (negative) হয়ে যেতে পারে — সিস্টেম এটি আটকায় না,
   শুধু লেজারে সঠিকভাবে রেকর্ড করে। Cancel করার আগে ম্যানুয়ালি স্টক চেক করে নেওয়া ভালো।

---

## Phase 6-এ যা যা আছে (Return & Exchange Management)

- ✅ **Sales Return** — Dealer/Retailer থেকে পণ্য ফেরত; কনফার্ম হলে স্টক ফিরে আসে
  (`return_in`) এবং তাদের পাওনা কমে যায় (ব্যালেন্স স্বয়ংক্রিয় আপডেট)
- ✅ **Purchase Return** — Supplier-কে পণ্য ফেরত; স্টক কমে যায় (`return_out`) এবং
  আমাদের দেনা কমে যায়
- ✅ মূল ইনভয়েস নম্বর (ঐচ্ছিক, রেফারেন্সের জন্য) ও কারণ (reason) লেখার সুযোগ
- ✅ এক ক্লিকে সম্পূর্ণ **Cancel/Reversal** (Sales/Purchase-এর মতোই)

**📝 Exchange সম্পর্কে স্পষ্টীকরণ:** এই সিস্টেমে "Exchange" কে আলাদা transaction টাইপ
হিসেবে না বানিয়ে, বরং Return + নতুন Sale/invoice — এই দুই ধাপের সমন্বয়ে করার জন্য
ডিজাইন করা হয়েছে (যেমন বাস্তব দোকানেও হয়: আগে পুরনো পণ্য ফেরত নেওয়া, তারপর নতুন পণ্য
বিক্রি করা)। এতে একই স্টক-লেজার ও ব্যালেন্স-লজিক পুনর্ব্যবহার হয়, আলাদা কোনো জটিল
"exchange" ইঞ্জিন বানাতে হয়নি — কম কোড, কম বাগের ঝুঁকি।

---

## Phase 7-এ যা যা আছে (KPI, Commission & Reward)

- ✅ **মাসিক Target** — SR-এর জন্য (তাদের করা Secondary Sales-এর ভিত্তিতে) অথবা
  Dealer-এর জন্য (সেই Dealer-কে করা Primary Sales-এর ভিত্তিতে) টার্গেট নির্ধারণ
- ✅ **Achievement** সবসময় লাইভ ডেটা থেকে হিসাব হয় (Sales টেবিল থেকে সরাসরি — আলাদা
  কোনো "achieved amount" কলামে ম্যানুয়ালি আপডেট করতে হয় না)
- ✅ **Commission** — প্রতিটি SR ইউজারের প্রোফাইলে Commission Rate (%) সেট করা যায়
  (Admin → Users → Edit), এবং সেই রেট × অর্জিত বিক্রয় = কমিশন স্বয়ংক্রিয়ভাবে হিসাব
  হয়ে রিপোর্টে দেখায়
- ✅ **Leaderboard/Reward** — মাস অনুযায়ী SR-দের র‍্যাঙ্কিং, শীর্ষ ৩ জনের জন্য
  🥇🥈🥉 ট্রফি আইকন — এটাই "reward" ভিউ (আলাদা পয়েন্ট/ব্যাজ সিস্টেম প্রথম ভার্সনে রাখা
  হয়নি, প্রয়োজনে পরে যোগ করা যাবে)
- ✅ পারমিশন-ভিত্তিক scope: যার শুধু `kpi.view` আছে (যেমন SR নিজে) সে শুধু নিজের সারি
  দেখবে; যার `kpi.manage` আছে (Manager/Admin) সে সবার তথ্য ও কমিশন দেখবে

---

## Phase 8-এ যা যা আছে (Email Notification System)

সিস্টেমের যেকোনো গুরুত্বপূর্ণ ঘটনার তথ্য এখন ইমেইলে পাওয়া যাবে — একটি কেন্দ্রীয়
`NotificationDispatcher` সার্ভিসের মাধ্যমে, যাতে প্রতিটি ইমেইল **Notification
Settings** পেজ থেকে on/off করা যায় এবং প্রতিটি পাঠানোর চেষ্টা লগ হয়ে থাকে।

**যে ঘটনাগুলোতে স্বয়ংক্রিয়ভাবে ইমেইল যায়:**
- ✅ **নতুন ইউজার তৈরি** — সাময়িক পাসওয়ার্ডসহ Welcome ইমেইল (নতুন ইউজারের ইমেইলে)
- ✅ **লো-স্টক সতর্কতা** — কোনো Sales invoice-এর কারণে স্টক রি-অর্ডার লেভেলে নামলে
  (Inventory manage করতে পারা ইউজারদের কাছে)
- ✅ **নতুন সেলস ইনভয়েস** তৈরি হলে (Sales দেখতে পারা ইউজারদের কাছে)
- ✅ **নতুন ক্রয় (Purchase)** রেকর্ড হলে (Accounting দেখতে পারা ইউজারদের কাছে)
- ✅ **নতুন রিটার্ন** রেকর্ড হলে (Returns দেখতে পারা ইউজারদের কাছে)
- ✅ **এসআর দেরিতে চেক-ইন** করলে (সকাল ১০টার পরে — Live Tracking দেখতে পারা
  ম্যানেজারদের কাছে)
- ✅ **মাসিক টার্গেট ১০০% অর্জিত** হলে (একবারই পাঠানো হয়, বারবার না — `notified_at`
  কলাম দিয়ে ট্র্যাক করা হয়)
- ✅ **দৈনিক ব্যবসায়িক সারসংক্ষেপ** — প্রতিদিন রাতে একটি scheduled job-এর মাধ্যমে
  (সেলস/ক্রয়/রিটার্ন/এসআর অ্যাটেন্ডেন্স/খরচ/বকেয়ার সারসংক্ষেপ)
- ✅ **বকেয়া পার্টনার রিমাইন্ডার** — প্রতিদিন সকালে, যেসব Dealer/Retailer-এর কাছে
  বকেয়া আছে তাদের তালিকা

**Notification Settings পেজ** (Admin → Notification Settings) থেকে:
- প্রতিটি ইভেন্ট আলাদাভাবে on/off করা যায়
- প্রতিটি ইভেন্টে অতিরিক্ত ইমেইল (extra recipients) যোগ করা যায়
- **Send Test Email** বাটন দিয়ে SMTP কনফিগারেশন সঠিক কিনা সাথে সাথে যাচাই করা যায়
- সাম্প্রতিক ২০টি ইমেইলের Log (পাঠানো হয়েছে/ব্যর্থ হয়েছে, কারণসহ)

**⚠️ সৎভাবে জানানো সীমাবদ্ধতাসমূহ:**
1. **বকেয়া রিমাইন্ডার প্রকৃত aging-ভিত্তিক নয়** — কোনো নির্দিষ্ট ইনভয়েসের due date
   থেকে ৩০/৬০/৯০ দিন হিসাব করে না (যেহেতু Sale-এ কোনো "due date" ফিল্ড নেই); বরং
   partner-এর *বর্তমান মোট balance* দেখে পাঠায়। ভবিষ্যতে প্রকৃত invoice-aging চাইলে
   Sale টেবিলে due_date যোগ করে এই কমান্ড আপডেট করা যাবে।
2. **ইমেইল পাঠানোর জন্য queue ব্যবহার করা হয়েছে** (`->queue()`), তাই production-এ
   `php artisan queue:work` চালু রাখতে হবে — নাহলে ইমেইল queue-তে জমে থাকবে, পাঠানো
   হবে না। নিচে এর setup instruction আছে। (Test Email বাটনটি ব্যতিক্রম — সেটা সরাসরি
   synchronous ভাবে পাঠানো হয়, যাতে সাথে সাথে success/fail বোঝা যায়)
3. **Daily Summary ও Overdue Reminder** scheduled command হিসেবে বানানো হয়েছে, কিন্তু
   Laravel-এর scheduler কাজ করতে সার্ভারে একটি cron entry লাগবে (নিচে ধাপ আছে) —
   এটা ছাড়া এই দুটো ইমেইল কখনো স্বয়ংক্রিয়ভাবে পাঠানো হবে না।

---

## Installation (ধাপে ধাপে)

### ১. নতুন Laravel প্রজেক্ট তৈরি করুন
```bash
composer create-project laravel/laravel ridneel-dms
cd ridneel-dms
```

### ২. এই প্যাকেজের ফাইলগুলো কপি করুন
এই zip-এর ভেতরের ফোল্ডারগুলো আপনার নতুন Laravel প্রজেক্টে **একই path-এ** কপি/মার্জ করুন:

```
app/Http/Controllers/  →  app/Http/Controllers/   (Admin/, Auth/, Inventory/, Partners/, Sales/, Sr/, Accounting/, Purchases/, Returns/, Kpi/ সহ)
app/Http/Middleware/   →  app/Http/Middleware/
app/Models/             →  app/Models/  (User.php replace করুন)
app/Providers/          →  app/Providers/ (AppServiceProvider.php replace করুন)
app/Support/            →  app/Support/
app/Services/           →  app/Services/   (NotificationDispatcher — Phase 8)
app/Mail/               →  app/Mail/       (SystemNotificationMail — Phase 8)
app/Console/Commands/   →  app/Console/Commands/  (SendDailySummary, SendOverdueReminders — Phase 8)
database/migrations/    →  database/migrations/
database/seeders/       →  database/seeders/ (DatabaseSeeder.php replace করুন)
resources/views/        →  resources/views/   (emails/ সহ — Phase 8)
resources/lang/         →  resources/lang/
routes/web.php          →  routes/web.php (replace করুন)
public/css/app.css      →  public/css/app.css
public/js/app.js        →  public/js/app.js
```

এই zip-টি **সম্পূর্ণ প্রজেক্ট** (Phase 1 থেকে 8 — সব একসাথে), তাই নতুন ইনস্টলেশনে উপরের
ফোল্ডারগুলো একবারে কপি করলেই যথেষ্ট — ধাপে ধাপে আলাদা করে বসানোর দরকার নেই। যদি আপনি
এর আগে কোনো পুরনো (আংশিক) ভার্সন থেকে আপগ্রেড করে থাকেন, তাহলে নিরাপদ উপায় হলো পুরো
zip-এর কনটেন্ট দিয়ে আপনার প্রজেক্টের একই ফোল্ডারগুলো **সম্পূর্ণ replace** করা — বিশেষ
করে `routes/web.php`, `app/Support/Permissions.php`,
`database/seeders/RolePermissionSeeder.php`, `database/seeders/DatabaseSeeder.php`,
`resources/lang/*/app.php`, ও `resources/views/layouts/partials/sidebar.blade.php`
ফাইলগুলো — কারণ প্রতিটি ফেজ এই ফাইলগুলোতেই নতুন এন্ট্রি যোগ করেছে।

### ৩. Middleware রেজিস্টার করুন

**Laravel 11 / 12 হলে:** `bootstrap/app.php.snippet` ফাইলটি খুলুন — এতে exact instruction
দেওয়া আছে আপনার `bootstrap/app.php`-এর `->withMiddleware()` অংশে কী যোগ করতে হবে।

**Laravel 10 হলে:** `app/Http/Kernel.php.snippet` ফাইলটি দেখুন — একই জিনিস কিন্তু পুরনো
`Kernel.php` স্ট্রাকচারের জন্য।

(দুটোই শুধু নির্দেশনা — সরাসরি এই snippet ফাইল দুটো আপনার প্রজেক্টে কপি করবেন না,
শুধু এর ভেতরের কোড আপনার আসল ফাইলে বসাবেন।)

### ৪. Scheduler রেজিস্টার করুন (Phase 8-এর Daily Summary / Overdue Reminder-এর জন্য)

**Laravel 11 / 12 হলে:** `routes/console.php.snippet` দেখুন — আপনার
`routes/console.php`-এ দুটো লাইন যোগ করতে হবে।

**Laravel 10 হলে:** `app/Console/Kernel.php.snippet` দেখুন — আপনার
`app/Console/Kernel.php`-এর `schedule()` মেথডে যোগ করতে হবে।

### ৫. `.env` ফাইলে ডাটাবেজ কনফিগার করুন
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ridneel_dms
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

### ৬. `.env` ফাইলে Mail (SMTP) কনফিগার করুন (Phase 8)

যেকোনো SMTP প্রোভাইডার ব্যবহার করতে পারেন (Gmail, cPanel/hosting mail, SendGrid,
Mailgun, ইত্যাদি)। cPanel hosting-এর সাধারণ উদাহরণ:
```env
MAIL_MAILER=smtp
MAIL_HOST=mail.ridneeltradeinternational.com
MAIL_PORT=587
MAIL_USERNAME=noreply@ridneeltradeinternational.com
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@ridneeltradeinternational.com
MAIL_FROM_NAME="Ridneel DMS ERP"
```
Gmail ব্যবহার করতে চাইলে: `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`,
`MAIL_USERNAME`-এ আপনার Gmail address, আর `MAIL_PASSWORD`-এ সাধারণ পাসওয়ার্ড নয় —
Google Account-এর **App Password** ব্যবহার করতে হবে (2-Step Verification চালু থাকলে
Google Account → Security → App Passwords থেকে বানানো যায়)।

কনফিগার করার পর **Admin → Notification Settings** পেজে গিয়ে "Send Test Email" দিয়ে
যাচাই করে নিন।

### ৭. `config/app.php`-এ locale ঠিক করুন
```php
'locale' => 'bn',
'fallback_locale' => 'en',
```

### ৮. Migrate ও Seed করুন
```bash
php artisan migrate
php artisan db:seed
```
(আগে থেকে migrate করা থাকলে, শুধু `php artisan migrate` আবার চালালেই নতুন টেবিলগুলো
তৈরি হয়ে যাবে — পুরনো ডেটা মুছে যাবে না। প্রয়োজনমতো আলাদাভাবে সিড করতে:
`php artisan db:seed --class=RolePermissionSeeder`,
`php artisan db:seed --class=AccountHeadSeeder`,
`php artisan db:seed --class=NotificationSettingSeeder`)

এটি চলার পর একটি ডিফল্ট Super Admin তৈরি হবে:
```
Email: admin@ridneeltrade.com
Password: ChangeMe@123
```
**⚠️ প্রথম লগইনের পরেই এই পাসওয়ার্ড অবশ্যই পরিবর্তন করুন।**
(Password change ফিচার Phase 1-এ user নিজে edit করতে পারবে না — এটা ইচ্ছাকৃতভাবে Admin →
Users → Edit থেকে করতে হবে; self-service "profile settings" পেজ ছোট addition হিসেবে
পরের মেসেজেই চাইলে যোগ করে দিতে পারি।)

### ৯. Storage link তৈরি করুন (Phase 4-এর attendance ছবি দেখানোর জন্য আবশ্যক)
```bash
php artisan storage:link
```
এটি না চালালে Attendance Report-এ ছবির thumbnail দেখাবে না (ছবি সংরক্ষিত হবে, শুধু
ব্রাউজারে দেখানো যাবে না)।

### ১০. Queue worker চালু করুন (Phase 8 — ইমেইল পাঠানোর জন্য আবশ্যক)

সব ইমেইল queue-তে যায় (synchronous ভাবে না, যাতে Sale/Purchase সেভ করার সময় ধীরগতি
না হয়)। `.env`-এ:
```env
QUEUE_CONNECTION=database
```
তারপর:
```bash
php artisan queue:table
php artisan migrate
php artisan queue:work --daemon
```
প্রোডাকশনে এই `queue:work` প্রসেসটা সবসময় চালু রাখতে হবে — [Supervisor](https://laravel.com/docs/queues#supervisor-configuration)
দিয়ে চালানো সবচেয়ে নির্ভরযোগ্য উপায় (cPanel hosting হলে "Setup Node.js/Python App" এর
পাশে অনেক hosting-এ "Cron Job" দিয়েও `queue:work --stop-when-empty` প্রতি মিনিটে চালানো
যায়)। যদি queue সেটআপ করতে না চান/না পারেন, `.env`-এ সাময়িকভাবে `QUEUE_CONNECTION=sync`
দিন — তাহলে ইমেইল সাথে সাথে (synchronous) পাঠানো হবে, আলাদা worker লাগবে না, কিন্তু
SMTP সার্ভার ধীরগতির হলে Sale/Purchase সেভ করতেও সময় বেশি লাগবে।

### ১১. Scheduler-এর জন্য Cron Job বসান (Phase 8 — Daily Summary/Overdue Reminder-এর জন্য)

আপনার সার্ভারে crontab-এ এই একটি লাইন যোগ করুন (cPanel হলে "Cron Jobs" থেকে):
```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```
এটি Laravel-এর নিজস্ব scheduler চালু করবে, যেটা Step ৪-এ রেজিস্টার করা
`app:send-daily-summary` ও `app:send-overdue-reminders` কমান্ড দুটো তাদের নির্ধারিত
সময়ে নিজে থেকে চালাবে।

### ১২. সার্ভার চালু করুন
```bash
php artisan serve
```
ব্রাউজারে যান: `http://127.0.0.1:8000`

(লোকাল ডেভেলপমেন্টে Step ১০-১১ ঐচ্ছিক — `QUEUE_CONNECTION=sync` রেখে এবং কমান্ড দুটো
ম্যানুয়ালি টেস্ট করতে `php artisan app:send-daily-summary` সরাসরি চালিয়েও দেখা যায়।)

---

## Folder এ কী কী ফাইল আছে তার সারাংশ (Phase 1–8 মিলিয়ে, সম্পূর্ণ প্রজেক্ট)

| এরিয়া | ফাইল সংখ্যা | নোট |
|---|---|---|
| Controllers | 26 | Auth, Dashboard, Language, Admin (Role/User/NotificationSetting), Inventory (5), Partners, Sales, Sr (Face/Attendance/Tracking — 3), Accounting (AccountHead/Expense/Voucher/Ledger/Report — 5), Purchases, Returns, Kpi (Target/Report — 2) |
| Middleware | 2 | CheckPermission, SetLocale |
| Models | 23 | User, Role, Category, Unit, Warehouse, Product, Partner, StockMovement, Sale, SaleItem, PartnerTransaction, SrFaceProfile, SrAttendance, SrLocation, AccountHead, Expense, Purchase, PurchaseItem, ReturnTransaction, ReturnItem, SalesTarget, NotificationSetting, NotificationLog |
| Migrations | 26 | Phase 1 থেকে 8 পর্যন্ত সব টেবিল/কলাম পরিবর্তন |
| Seeders | 4 | RolePermissionSeeder, AccountHeadSeeder, NotificationSettingSeeder, DatabaseSeeder |
| Services | 1 | NotificationDispatcher — Phase 8-এর সব ইমেইল এর মধ্য দিয়ে যায় |
| Mail | 1 | SystemNotificationMail — একটাই reusable Mailable, ভিন্ন ভিন্ন Blade ভিউ দিয়ে ১০টা ইমেইল টাইপ |
| Console Commands | 2 | SendDailySummary, SendOverdueReminders (scheduled) |
| Views (.blade.php) | 60 | layout/sidebar/topbar, প্রতিটি মডিউলের CRUD/ইনভয়েস/রিপোর্ট/print, emails/ (১০টা ইমেইল টেমপ্লেট) |
| Language files | 2 | `lang/bn/app.php`, `lang/en/app.php` — প্রতিটিতে ৩৭৬টি স্ট্রিং, এক্স্যাক্ট মিরর |
| CSS/JS | 2 | dark-mode-aware theme + toggle/sidebar behaviour (বাকি সব JS ভিউর ভেতরেই `@push('scripts')` দিয়ে লেখা) |

---

## 🎉 মূল রোডম্যাপ + Email Notifications সম্পূর্ণ

প্রথম মেসেজে যে ৬টা ফিচার (GPS/Face Attendance, Primary/Secondary Sales, Dealer/
Retailer/Mechanic ব্যবস্থাপনা, Inventory/Sales/Purchase/Accounting Integration,
Return/Exchange, KPI/Commission/Reward) চাওয়া হয়েছিল — Phase 1 থেকে 7 মিলিয়ে
**প্রতিটিই বাস্তব, কাজ-করা কোড হিসেবে আছে।** এরপর অতিরিক্ত হিসেবে **Phase 8 (Email
Notification System)** যোগ করা হয়েছে, যাতে সিস্টেমের গুরুত্বপূর্ণ তথ্য (নতুন ইনভয়েস,
লো-স্টক, দেরিতে চেক-ইন, টার্গেট অর্জন, দৈনিক সারসংক্ষেপ, বকেয়া রিমাইন্ডার) সরাসরি
ইমেইলে পাওয়া যায়। দ্বিভাষা (বাংলা/English), Dark Mode, Role & Permission — সবই শুরু
থেকে প্রতিটি মডিউলে প্রযোজ্য।

এর মানে এই না যে সিস্টেমটা আর বাড়ানো যাবে না — বরং এখন থেকে যেকোনো সংযোজন "নতুন Phase"
না হয়ে "নির্দিষ্ট ফিচার request" হবে। নিচে কিছু স্বাভাবিক পরবর্তী ধাপের উদাহরণ, যেগুলোর
যেকোনোটা বললেই কাজ শুরু করে দেব:

- **SMS নোটিফিকেশন** — ইমেইলের পাশাপাশি/বদলে SMS gateway (যেমন bulk SMS BD প্রোভাইডার)
  ইন্টিগ্রেশন
- **Dealer/Retailer/Mechanic Mobile App** — এই মুহূর্তে সবকিছুই একটা রেসপনসিভ ওয়েব
  অ্যাপ হিসেবে কাজ করছে (ফোনেও ব্যবহারযোগ্য); সত্যিকারের নেটিভ অ্যাপ (Play Store/App
  Store-এ পাবলিশ করার মতো) চাইলে সেটা একটা আলাদা, বড় কাজ
- README-তে উল্লেখিত সীমাবদ্ধতাগুলো (COGS আনুমানিক, ফেস-ম্যাচিং ক্লায়েন্ট-সাইড, বকেয়া
  রিমাইন্ডার true-aging নয়, ইত্যাদি) harden করা
- নির্দিষ্ট কোনো মডিউলে আরও গভীর ফিচার (যেমন Purchase-এ multi-currency, Sales-এ
  discount approval workflow, ইত্যাদি)
- পুরো সিস্টেমের UI পলিশ, রিপোর্ট PDF/Excel এক্সপোর্ট
- Production deployment সহায়তা (server setup, Supervisor/queue worker, backup strategy)

যা-ই লাগুক, "চালিয়ে যাও" বা নির্দিষ্ট ফিচারের নাম বললেই শুরু করে দেব।
#   r i d n e e l _ e r p  
 