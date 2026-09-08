@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="login-page">
    <div class="login-card">
        <div class="text-center mb-4">
            <img src="{{ asset('images/albadar.png') }}" alt="Al Badar" class="brand-logo brand-logo-login">
            <h1>Al Badar</h1>
            <p class="text-muted mb-0">Roznamcha / ERP System</p>
        </div>

        @include('partials.alerts')

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Username / Email</label>
                <input type="text" name="email" value="{{ old('email') }}" class="form-control form-control-lg" required autofocus autocomplete="username">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control form-control-lg" required autocomplete="current-password">
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" @checked(old('remember'))>
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <button class="btn btn-accent btn-lg w-100">Login</button>
        </form>
        <div class="text-center mt-4">
            @include('partials.footer')
        </div>
    </div>
</div>
@endsection
