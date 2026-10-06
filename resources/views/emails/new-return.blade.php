@extends('emails.layout')

@section('content')
<h2 style="margin:0 0 12px; font-size:20px; color:#212529;">↩️ নতুন রিটার্ন রেকর্ড হয়েছে</h2>

<table role="presentation" width="100%" cellpadding="8" cellspacing="0" style="background:#f8f9fa; border-radius:8px; margin:16px 0; font-size:14px;">
    <tr><td style="color:#868e96; width:160px;">রিটার্ন নং</td><td style="font-weight:600;">{{ $returnNo }}</td></tr>
    <tr><td style="color:#868e96;">ধরন</td><td style="font-weight:600;">{{ $typeLabel }}</td></tr>
    <tr><td style="color:#868e96;">পার্টনার</td><td style="font-weight:600;">{{ $partnerName }}</td></tr>
    <tr><td style="color:#868e96;">কারণ</td><td style="font-weight:600;">{{ $reason ?: '—' }}</td></tr>
    <tr><td style="color:#868e96;">মোট</td><td style="font-weight:600;">{{ $total }}</td></tr>
    <tr><td style="color:#868e96;">তৈরি করেছেন</td><td style="font-weight:600;">{{ $createdBy }}</td></tr>
</table>

<a href="{{ $url }}" style="display:inline-block; padding:10px 20px; background:#0d6efd; color:#fff; text-decoration:none; border-radius:6px; font-size:14px;">রিটার্ন দেখুন</a>
@endsection
