<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --primary: #2563eb; --primary-dark: #1d4ed8; }
        body { min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.08); }
        .hero { background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%); color: #fff; padding: 80px 0; }
        .card-product { transition: transform .2s, box-shadow .2s; }
        .card-product:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }
        .price { color: var(--primary); font-weight: 700; font-size: 1.25rem; }
        .price-old { text-decoration: line-through; color: #9ca3af; font-size: .875rem; }
        .badge-stock { font-size: .75rem; }
        .sidebar-admin { min-height: calc(100vh - 56px); background: #1e293b; }
        .sidebar-admin .nav-link { color: #94a3b8; padding: 10px 20px; }
        .sidebar-admin .nav-link:hover, .sidebar-admin .nav-link.active { color: #fff; background: rgba(255,255,255,.1); }
        .stat-card { border-left: 4px solid; border-radius: 8px; }
        footer { background: #1e293b; color: #94a3b8; }
    </style>
    @yield('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">
                <i class="bi bi-shop"></i> {{ config('app.name') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('categories.index') }}">Categories</a></li>
                </ul>
                <ul class="navbar-nav">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Admin</a></li>
                        @endif
                        @if(auth()->user()->isEmployee())
                            <li class="nav-item"><a class="nav-link" href="{{ route('employee.orders.index') }}"><i class="bi bi-briefcase"></i> Employee</a></li>
                        @endif
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('customer.orders.index') }}">My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="dropdown-item" type="submit">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    @if(session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="container mt-3">
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <main class="flex-grow-1">
        @yield('content')
    </main>

    <footer class="py-4 mt-auto">
        <div class="container text-center">
            <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
