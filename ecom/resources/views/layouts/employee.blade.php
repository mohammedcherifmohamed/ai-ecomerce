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
        .admin-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #1e293b; color: #e2e8f0; flex-shrink: 0; }
        .sidebar .nav-link { color: #94a3b8; padding: 12px 20px; display: flex; align-items: center; gap: 10px; border-radius: 6px; margin: 2px 10px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,.1); }
        .sidebar .nav-link i { width: 20px; text-align: center; }
        .sidebar-brand { padding: 20px; font-size: 1.1rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,.1); }
        .admin-content { flex: 1; background: #f1f5f9; }
        .admin-topbar { background: #fff; padding: 15px 25px; box-shadow: 0 1px 3px rgba(0,0,0,.08); display: flex; justify-content: space-between; align-items: center; }
        .stat-card { border-radius: 10px; border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    </style>
    @yield('styles')
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <a href="{{ route('employee.orders.index') }}" class="text-white text-decoration-none">
                    <i class="bi bi-shop"></i> {{ config('app.name') }}
                </a>
            </div>
            <nav class="mt-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('employee.orders.*') ? 'active' : '' }}" href="{{ route('employee.orders.index') }}"><i class="bi bi-receipt"></i> Orders</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('employee.inventory.*') ? 'active' : '' }}" href="{{ route('employee.inventory.index') }}"><i class="bi bi-box-seam"></i> Inventory</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('employee.customers.*') ? 'active' : '' }}" href="{{ route('employee.customers.index') }}"><i class="bi bi-people"></i> Customers</a></li>
                    <li class="nav-item mt-3"><hr class="text-secondary mx-3"></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}"><i class="bi bi-arrow-left"></i> Back to Store</a></li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="nav-link text-start w-100 border-0 bg-transparent" type="submit"><i class="bi bi-box-arrow-left"></i> Logout</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </aside>
        <div class="admin-content">
            <div class="admin-topbar">
                <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
                <span>{{ auth()->user()->name }}</span>
            </div>
            <div class="p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
