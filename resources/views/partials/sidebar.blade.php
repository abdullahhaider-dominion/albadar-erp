<aside class="sidebar" id="appSidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('images/albadar.png') }}" alt="Al Badar" class="brand-logo">
        <div class="brand-title">{{ \App\Models\Setting::companyName() }}</div>
        <div class="brand-sub">Roznamcha / ERP System</div>
    </div>
    <nav class="nav flex-column py-2">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a class="nav-link {{ request()->routeIs('roznamcha.*') ? 'active' : '' }}" href="{{ route('roznamcha.index') }}">
            <i class="bi bi-journal-text"></i> Roznamcha
        </a>

        <div class="nav-section">Add Entry</div>
        <a class="nav-link {{ request()->routeIs('entries.income.*') ? 'active' : '' }}" href="{{ route('entries.income.create') }}">
            <i class="bi bi-plus-circle"></i> Add Aamdan
        </a>
        <a class="nav-link {{ request()->routeIs('entries.expense.*') ? 'active' : '' }}" href="{{ route('entries.expense.create') }}">
            <i class="bi bi-dash-circle"></i> Add Kharcha
        </a>

        @if(auth()->user()->isAdmin())
            <div class="nav-section">Admin</div>
            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                <i class="bi bi-bar-chart"></i> Reports
            </a>
            <a class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}">
                <i class="bi bi-clock-history"></i> Audit Log
            </a>
            <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                <i class="bi bi-tags"></i> Expense Categories
            </a>
            <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                <i class="bi bi-people"></i> Users
            </a>
            <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        @endif
    </nav>
</aside>
