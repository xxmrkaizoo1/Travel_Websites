@component('mail::message')
# Verify your email

Hello {{ $clientName }},

Use this one-time code to verify your Smart Holidays client account:

@component('mail::panel')
<div style="font-size: 30px; font-weight: 700; letter-spacing: 8px; text-align: center;">{{ $code }}</div>
@endcomponent

This code expires in **10 minutes**. If you did not request this, you can ignore this email.

Thanks,<br>
{{ config('app.name', 'Smart Holidays') }}
@endcomponent
