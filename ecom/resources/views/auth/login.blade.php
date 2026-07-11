@extends('layouts.guest')
@section('title', 'Login')

@section('content')
<div class="card auth-card p-4">
    <div class="text-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-shop text-primary"></i></h2>
        <h4>Sign In</h4>
        <p class="text-muted">Welcome back! Please sign in to your account.</p>
    </div>
    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>
        <button type="submit" class="btn btn-primary w-100">Sign In</button>
    </form>
    <div class="text-center mt-3">
        <p class="text-muted">Don't have an account? <a href="{{ route('register') }}">Register</a></p>
    </div>
</div>
@endsection
