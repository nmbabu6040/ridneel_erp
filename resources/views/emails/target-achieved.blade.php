@extends('emails.layout')

@section('content')
<h2 style="margin:0 0 12px; font-size:20px; color:#198754;">🏆 টার্গেট অর্জিত হয়েছে!</h2>
<p style="font-size:14px; color:#495057; line-height:1.6;">অভিনন্দন! নিচের টার্গেট ১০০% বা তার বেশি অর্জিত হয়েছে:</p>

<table role="presentation" width="100%" cellpadding="8" cellspacing="0" style="background:#f0fff4; border-radius:8px; margin:16px 0; font-size:14px; border:1px solid #b2f2bb;">
    <tr><td style="color:#868e96; width:160px;">নাম</td><td style="font-weight:600;">{{ $subjectName }}</td></tr>
    <tr><td style="color:#868e96;">মাস</td><td style="font-weight:600;">{{ $month }}</td></tr>
    <tr><td style="color:#868e96;">টার্গেট</td><td style="font-weight:600;">{{ $targetAmount }}</td></tr>
    <tr><td style="color:#868e96;">অর্জিত</td><td style="font-weight:600; color:#198754;">{{ $achieved }} ({{ $percent }}%)</td></tr>
</table>
@endsection
