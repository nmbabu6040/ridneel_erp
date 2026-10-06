@extends('emails.layout')

@section('content')
<h2 style="margin:0 0 12px; font-size:20px; color:#212529;">স্বাগতম, {{ $name }}!</h2>
<p style="font-size:14px; color:#495057; line-height:1.6;">
    Ridneel DMS ERP-এ আপনার জন্য একটি অ্যাকাউন্ট তৈরি করা হয়েছে। নিচের তথ্য দিয়ে লগইন করুন:
</p>

<table role="presentation" width="100%" cellpadding="8" cellspacing="0" style="background:#f8f9fa; border-radius:8px; margin:16px 0; font-size:14px;">
    <tr><td style="color:#868e96; width:140px;">ইমেইল</td><td style="font-weight:600;">{{ $email }}</td></tr>
    <tr><td style="color:#868e96;">সাময়িক পাসওয়ার্ড</td><td style="font-weight:600;">{{ $password }}</td></tr>
    <tr><td style="color:#868e96;">রোল</td><td style="font-weight:600;">{{ $roleName }}</td></tr>
</table>

<p style="font-size:13px; color:#dc3545;">⚠️ নিরাপত্তার জন্য প্রথম লগইনের পরেই পাসওয়ার্ড পরিবর্তন করুন।</p>

<a href="{{ $loginUrl }}" style="display:inline-block; margin-top:8px; padding:10px 20px; background:#0d6efd; color:#fff; text-decoration:none; border-radius:6px; font-size:14px;">লগইন করুন</a>
@endsection
