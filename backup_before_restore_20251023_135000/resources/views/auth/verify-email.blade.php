{{-- Email verification page --}}
@extends('front.layout.front-layout')

@section('content')
<div class="container text-center">
    <h2>Email Verification Required</h2>
    <p>Your email verification link is invalid or expired. Please request a new verification email.</p>
    <a href="{{ route('verification.resend') }}" class="btn btn-primary">Resend Verification Email</a>
</div>
@endsection
