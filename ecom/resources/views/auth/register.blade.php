@extends('layouts.guest')
@section('title', 'Register')

@section('content')
<div class="card auth-card p-4">
    <div class="text-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-shop text-primary"></i></h2>
        <h4>Create Account</h4>
        <p class="text-muted">Join us and start shopping today.</p>
    </div>
    <form method="POST" action="{{ route('register.post') }}">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="city" class="form-label">City</label>
                <input type="text" class="form-control" id="city" name="city" value="{{ old('city') }}">
            </div>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" class="form-control" id="address" name="address" value="{{ old('address') }}">
        </div>
        <button type="submit" class="btn btn-primary w-100">Create Account</button>
    </form>
    <div class="text-center mt-3">
        <p class="text-muted">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
    </div>
</div>
@endsection
