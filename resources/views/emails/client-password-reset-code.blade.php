@extends('emails.layouts.brand')

@section('preheader', "Your Smart Holidays password reset code is {$code}. It expires in 10 minutes.")
@section('eyebrow', 'PASSWORD RESET')
@section('title', 'Reset your password')

@section('content')
    <p style="margin: 16px 0 0; color: #3d4f6a; font-size: 15px; line-height: 1.7;">Hello {{ $clientName }},</p>
    <p style="margin: 10px 0 0; color: #3d4f6a; font-size: 15px; line-height: 1.7;">
        We received a request to reset the password for your Smart Holidays client account. Enter this code on the reset page, then choose a new password.
    </p>

    @include('emails.partials.otp-code', ['code' => $code])

    <p style="margin: 18px 0 0; color: #66748a; font-size: 13px; line-height: 1.6;">
        Didn’t ask to reset your password? You can ignore this email and your password will stay the same.
    </p>
@endsection
