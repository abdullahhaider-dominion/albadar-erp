<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') — {{ \App\Models\Setting::companyName() }}</title>
    <link rel="icon" href="{{ asset('images/albadar.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            <div class="d-flex align-items-center gap-2 min-w-0">
                <button class="btn btn-outline-secondary sidebar-toggle" type="button" id="sidebarToggle" aria-label="Open menu">
                    <i class="bi bi-list"></i>
                </button>
                <img src="{{ asset('images/albadar.png') }}" alt="Al Badar" class="brand-logo brand-logo-top d-lg-none">
                <div class="min-w-0">
                    <div class="fw-semibold text-truncate">@yield('page_title', 'Dashboard')</div>
                    <div class="text-muted small text-truncate">@yield('page_subtitle', 'Roznamcha / ERP System')</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 gap-md-3">
                <span class="text-muted small d-none d-sm-inline text-truncate">{{ auth()->user()->name }} · {{ ucfirst(auth()->user()->role) }}</span>
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
        <footer class="app-footer no-print">
            @include('partials.footer')
        </footer>
    </div>
</div>
@include('partials.modals')
@else
    @yield('content')
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebar = document.getElementById('appSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const toggle = document.getElementById('sidebarToggle');
    if (toggle && sidebar) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
        });
        backdrop?.addEventListener('click', () => {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        });
    }

    document.querySelectorAll('.js-range-select').forEach((select) => {
        select.addEventListener('change', function () {
            this.form.querySelectorAll('.custom-dates').forEach(el => {
                el.style.display = this.value === 'custom' ? '' : 'none';
            });
        });
    });
</script>
@stack('scripts')
</body>
</html>
