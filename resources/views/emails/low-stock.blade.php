@extends('emails.layout')

@section('content')
<h2 style="margin:0 0 12px; font-size:20px; color:#dc3545;">⚠️ লো-স্টক সতর্কতা</h2>
<p style="font-size:14px; color:#495057; line-height:1.6;">
    নিচের পণ্যের স্টক রি-অর্ডার লেভেলে পৌঁছেছে বা এর নিচে নেমে গেছে:
</p>

<table role="presentation" width="100%" cellpadding="8" cellspacing="0" style="background:#fff5f5; border-radius:8px; margin:16px 0; font-size:14px; border:1px solid #ffc9c9;">
    <tr><td style="color:#868e96; width:160px;">পণ্য</td><td style="font-weight:600;">{{ $productName }} ({{ $sku }})</td></tr>
    <tr><td style="color:#868e96;">গুদাম</td><td style="font-weight:600;">{{ $warehouseName }}</td></tr>
    <tr><td style="color:#868e96;">বর্তমান স্টক</td><td style="font-weight:600; color:#dc3545;">{{ $currentStock }} {{ $unit }}</td></tr>
    <tr><td style="color:#868e96;">রি-অর্ডার লেভেল</td><td style="font-weight:600;">{{ $reorderLevel }} {{ $unit }}</td></tr>
</table>

<p style="font-size:14px; color:#495057;">অনুগ্রহ করে এই পণ্যের জন্য নতুন ক্রয় (Purchase) এন্ট্রি করার কথা বিবেচনা করুন।</p>
@endsection
