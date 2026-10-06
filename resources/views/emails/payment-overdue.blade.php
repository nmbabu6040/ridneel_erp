@extends('emails.layout')

@section('content')
<h2 style="margin:0 0 12px; font-size:20px; color:#dc3545;">💰 বকেয়া পার্টনার তালিকা</h2>
<p style="font-size:14px; color:#495057;">নিচের ডিলার/রিটেইলারদের কাছে বর্তমানে বকেয়া জমা আছে:</p>

<table role="presentation" width="100%" cellpadding="8" cellspacing="0" style="font-size:14px; border-collapse:collapse;">
    <tr style="background:#f8f9fa;">
        <td style="padding:8px; font-weight:600; border-bottom:1px solid #dee2e6;">নাম</td>
        <td style="padding:8px; font-weight:600; border-bottom:1px solid #dee2e6;">ধরন</td>
        <td style="padding:8px; font-weight:600; border-bottom:1px solid #dee2e6; text-align:right;">বকেয়া</td>
    </tr>
    @foreach ($partners as $p)
    <tr>
        <td style="padding:8px; border-bottom:1px solid #f1f3f5;">{{ $p['name'] }}</td>
        <td style="padding:8px; border-bottom:1px solid #f1f3f5;">{{ $p['type'] }}</td>
        <td style="padding:8px; border-bottom:1px solid #f1f3f5; text-align:right; color:#dc3545; font-weight:600;">{{ $p['balance'] }}</td>
    </tr>
    @endforeach
</table>
@endsection
