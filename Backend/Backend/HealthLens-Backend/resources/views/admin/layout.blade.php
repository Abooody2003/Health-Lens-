<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - HealthLens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
            color: white;
            padding: 20px 0;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
            height: 110px;
            min-height: 110px;
            max-height: 110px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-sizing: border-box;
        }
        
        .sidebar-header h4 {
            margin: 0;
            padding: 0;
            font-weight: 600;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            max-width: 100%;
            max-height: 100%;
        }
        
        .sidebar-header h4 img {
            max-width: 80px;
            max-height: 80px;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin: 5px 0;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: #60a5fa;
        }
        
        .sidebar-menu a i {
            margin-right: 12px;
            width: 20px;
        }
        
        .sidebar-menu a img {
            margin-right: 12px;
            width: 20px;
            height: 20px;
            object-fit: contain;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        .header {
            background: white;
            height: var(--header-height);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .content-wrapper {
            padding: 30px;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 20px;
            font-weight: 600;
            border-radius: 10px 10px 0 0;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .stats-card.blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        
        .stats-card.green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .stats-card.orange {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .stats-card.purple {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }
        
        .stats-value {
            font-size: 32px;
            font-weight: 700;
            margin: 10px 0;
        }
        
        .stats-label {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .table {
            background: white;
        }
        
        .table thead {
            background-color: #f9fafb;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .btn {
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 500;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h4 {
            color: #374151;
            margin-bottom: 10px;
        }
        
        .pagination {
            margin-top: 20px;
        }
        
        .pagination .page-link {
            border-radius: 6px;
            margin: 0 2px;
            border: 1px solid #e5e7eb;
            color: #4b5563;
        }
        
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .table-actions {
            white-space: nowrap;
        }
        
        .table-actions .btn {
            margin: 0 2px;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h4><img src="{{ asset('images/dashboard-icon.png') }}" alt="HealthLens" /></h4>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <img src="{{ asset('images/dashboard-icon.png') }}" alt="Dashboard" />
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.doctors.index') }}" class="{{ request()->routeIs('admin.doctors.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i>
                    <span>Doctors</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.specializations.index') }}" class="{{ request()->routeIs('admin.specializations.*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i>
                    <span>Specializations</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.surgery-analyses.index') }}" class="{{ request()->routeIs('admin.surgery-analyses.*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-data"></i>
                    <span>Surgery Analyses</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="header">
            <div>
                <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted">{{ auth()->user()->full_name ?? auth()->user()->email }}</span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </header>

        <!-- Content -->
        <div class="content-wrapper">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Success!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Error!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <strong>Warning!</strong> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Info:</strong> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Validation Error!</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

