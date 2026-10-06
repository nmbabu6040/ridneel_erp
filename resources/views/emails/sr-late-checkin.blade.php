@extends('emails.layout')

@section('content')
<h2 style="margin:0 0 12px; font-size:20px; color:#fd7e14;">⏰ দেরিতে চেক-ইন</h2>

<table role="presentation" width="100%" cellpadding="8" cellspacing="0" style="background:#fff9f0; border-radius:8px; margin:16px 0; font-size:14px; border:1px solid #ffe0b2;">
    <tr><td style="color:#868e96; width:160px;">এসআর</td><td style="font-weight:600;">{{ $srName }}</td></tr>
    <tr><td style="color:#868e96;">চেক-ইন সময়</td><td style="font-weight:600;">{{ $checkInTime }}</td></tr>
    <tr><td style="color:#868e96;">তারিখ</td><td style="font-weight:600;">{{ $date }}</td></tr>
</table>
@endsection
