@extends('emails.layout')

@section('content')
<h2 style="margin:0 0 4px; font-size:20px; color:#212529;">📊 দৈনিক সারসংক্ষেপ</h2>
<p style="margin:0 0 16px; font-size:13px; color:#868e96;">{{ $date }}</p>

<table role="presentation" width="100%" cellpadding="10" cellspacing="0" style="font-size:14px; border-collapse:collapse;">
    <tr>
        <td style="background:#f8f9fa; border-radius:8px; padding:14px;">
            <div style="color:#868e96; font-size:12px;">সেলস ইনভয়েস</div>
            <div style="font-size:20px; font-weight:700;">{{ $salesCount }}</div>
        </td>
        <td style="width:12px;"></td>
        <td style="background:#f8f9fa; border-radius:8px; padding:14px;">
            <div style="color:#868e96; font-size:12px;">মোট বিক্রয়</div>
            <div style="font-size:20px; font-weight:700;">{{ $salesTotal }}</div>
        </td>
    </tr>
</table>

<table role="presentation" width="100%" cellpadding="8" cellspacing="0" style="background:#f8f9fa; border-radius:8px; margin:16px 0; font-size:14px;">
    <tr><td style="color:#868e96; width:200px;">নতুন ক্রয় (Purchase)</td><td style="font-weight:600;">{{ $purchaseCount }} — {{ $purchaseTotal }}</td></tr>
    <tr><td style="color:#868e96;">রিটার্ন</td><td style="font-weight:600;">{{ $returnCount }}</td></tr>
    <tr><td style="color:#868e96;">এসআর চেক-ইন</td><td style="font-weight:600;">{{ $srCheckedIn }} / {{ $srTotal }}</td></tr>
    <tr><td style="color:#868e96;">খরচ</td><td style="font-weight:600;">{{ $expenseTotal }}</td></tr>
    <tr><td style="color:#868e96;">মোট বকেয়া (প্রাপ্য)</td><td style="font-weight:600; color:#dc3545;">{{ $totalReceivable }}</td></tr>
</table>

<a href="{{ $url }}" style="display:inline-block; padding:10px 20px; background:#0d6efd; color:#fff; text-decoration:none; border-radius:6px; font-size:14px;">ড্যাশবোর্ড দেখুন</a>
@endsection
