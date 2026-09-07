@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="login-page">
    <div class="login-card">
        <div class="mb-4">
            <h1>Roznamcha ERP</h1>
            <p class="text-muted mb-0">Sign in to manage daily Aamdan & Kharcha</p>
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
        <p class="text-muted small mt-4 mb-0 text-center">Default admin: admin@roznamcha.local / password</p>
    </div>
</div>
@endsection
