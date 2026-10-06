@extends('emails.layout')

@section('content')
<h2 style="margin:0 0 12px; font-size:20px; color:#198754;">✅ টেস্ট ইমেইল সফল!</h2>
<p style="font-size:14px; color:#495057; line-height:1.6;">
    আপনার SMTP কনফিগারেশন সঠিকভাবে কাজ করছে। এই ইমেইলটি পাঠানো হয়েছে: <strong>{{ $sentAt }}</strong>
</p>
@endsection
