<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ \App\Models\Setting::companyName() }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
@auth
<div class="app-shell">
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    @include('partials.sidebar')
    <div class="main-wrap">
        <div class="topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm sidebar-toggle" type="button" id="sidebarToggle" aria-label="Menu">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <div class="fw-semibold">@yield('page_title', 'Dashboard')</div>
                    <div class="text-muted small">@yield('page_subtitle', 'Daily bookkeeping')</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small d-none d-sm-inline">{{ auth()->user()->name }} · {{ ucfirst(auth()->user()->role) }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger">Logout</button>
                </form>
            </div>
        </div>
        <main class="content">
            @include('partials.alerts')
            @yield('content')
        </main>
    </div>
</div>
@else
    @yield('content')
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebar = document.getElementById('appSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const toggle = document.getElementById('sidebarToggle');
    if (toggle) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
        });
        backdrop.addEventListener('click', () => {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        });
    }
</script>
@stack('scripts')
</body>
</html>
