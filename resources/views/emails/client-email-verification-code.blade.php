@extends('emails.layouts.brand')

@section('preheader', "Your Smart Holidays verification code is {$code}. It expires in 10 minutes.")
@section('eyebrow', 'VERIFY YOUR EMAIL')
@section('title', 'Welcome aboard!')

@section('content')
    <p style="margin: 16px 0 0; color: #3d4f6a; font-size: 15px; line-height: 1.7;">Hello {{ $clientName }},</p>
    <p style="margin: 10px 0 0; color: #3d4f6a; font-size: 15px; line-height: 1.7;">
        Thank you for creating a Smart Holidays client account. Enter this code on the verification page to finish setting up your account.
    </p>

    @include('emails.partials.otp-code', ['code' => $code])

    <p style="margin: 18px 0 0; color: #66748a; font-size: 13px; line-height: 1.6;">
        Didn’t sign up? You can safely ignore this email and no account will be created.
    </p>
@endsection
