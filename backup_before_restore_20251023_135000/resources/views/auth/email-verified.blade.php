@extends('front.layout.front-layout')

@section('content')
<div class="container text-center mt-5">
    <div class="card shadow-lg p-4">
        <img src="{{ $user->profile_photo_url ?? asset('assets/images/user.png') }}" 
             alt="User Photo" class="rounded-circle mb-3" width="100" height="100">
        
        <h2>Welcome, {{ $user->name }}!</h2>
        <p class="text-success">Your email has been successfully verified.</p>
        
            <!--<form action="{{ route('logout') }}" method="POST" style="display:inline;">-->
            <!--    @csrf-->
            <!--     <button type="button"  id="logoutButton" class="btn btn-danger">-->
            <!--            <i class="fa fa-sign-out-alt"></i> Logout-->
            <!--        </button>-->
            <!--</form>-->
    </div>
</div>
@endsection
