<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'NR Budget Tracker') }} - @yield('title', 'Personal Finance Management')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'pulse-slow': 'pulse 6s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --secondary: #6b7280;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
            
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            
            --radius-sm: 6px;
            --radius: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.5;
            color: var(--gray-800);
            background: var(--gray-50);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Mobile-First Layout */
        .app-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
            padding-bottom: 100px; /* Space for bottom nav */
        }

        /* Header */
        .header {
            background: white;
            border-bottom: 1px solid var(--gray-200);
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(10px);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            max-width: 100%;
        }

        .logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo i {
            font-size: 1.5rem;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .profile-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .profile-btn:hover {
            background: var(--primary-dark);
            transform: scale(1.05);
        }

        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid var(--gray-200);
            z-index: 50;
            padding: 0.5rem 0;
            backdrop-filter: blur(10px);
        }

        .nav-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0.25rem;
            max-width: 100%;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0.5rem 0.25rem;
            text-decoration: none;
            color: var(--gray-500);
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.2s;
            border-radius: var(--radius);
            position: relative;
        }

        .nav-item:hover,
        .nav-item.active {
            color: var(--primary);
            background: var(--gray-50);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            top: -0.5rem;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            background: var(--primary);
            border-radius: 50%;
        }

        .nav-item i {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow);
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .card-header {
            padding: 1rem 1.25rem;
            background: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
            font-weight: 600;
            color: var(--gray-800);
        }

        .card-body {
            padding: 1.25rem;
        }

        .card-footer {
            padding: 1rem 1.25rem;
            background: var(--gray-50);
            border-top: 1px solid var(--gray-200);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius-md);
            padding: 1.25rem;
            box-shadow: var(--shadow);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary);
        }

        .stat-card.income::before {
            background: var(--success);
        }

        .stat-card.expense::before {
            background: var(--danger);
        }

        .stat-card.balance::before {
            background: var(--info);
        }

        .stat-card.savings::before {
            background: var(--warning);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            opacity: 0.8;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: var(--gray-800);
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-600);
            font-weight: 500;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            font-weight: 500;
            border-radius: var(--radius);
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            min-height: 36px;
            position: relative;
            overflow: hidden;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .btn-secondary:hover {
            background: var(--gray-200);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-warning {
            background: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            min-height: 32px;
        }

        .btn-lg {
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            min-height: 44px;
        }

        .btn-full {
            width: 100%;
        }

        .btn-group {
            display: flex;
            gap: 0.5rem;
        }

        .btn-group .btn {
            flex: 1;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            font-size: 1rem;
            transition: all 0.2s;
            background: white;
            min-height: 48px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-control::placeholder {
            color: var(--gray-400);
        }

        .form-select {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            font-size: 1rem;
            background: white;
            min-height: 48px;
            cursor: pointer;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Tables */
        .table-container {
            background: white;
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        .table th {
            background: var(--gray-50);
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .table td {
            color: var(--gray-600);
        }

        .table tbody tr:hover {
            background: var(--gray-50);
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: var(--radius-sm);
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .badge-primary {
            background: var(--primary);
            color: white;
        }

        .badge-success {
            background: var(--success);
            color: white;
        }

        .badge-warning {
            background: var(--warning);
            color: white;
        }

        .badge-danger {
            background: var(--danger);
            color: white;
        }

        .badge-secondary {
            background: var(--gray-200);
            color: var(--gray-700);
        }

        /* Alerts */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            border: none;
            font-weight: 500;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
        }

        .alert-error {
            background: #fef2f2;
            color: #dc2626;
        }

        .alert-warning {
            background: #fffbeb;
            color: #92400e;
        }

        .alert-info {
            background: #f0f9ff;
            color: #1e40af;
        }

        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        .font-bold { font-weight: 700; }
        .font-semibold { font-weight: 600; }
        .font-medium { font-weight: 500; }
        
        .text-sm { font-size: 0.875rem; }
        .text-lg { font-size: 1.125rem; }
        .text-xl { font-size: 1.25rem; }
        .text-2xl { font-size: 1.5rem; }
        
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 0.75rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .mb-8 { margin-bottom: 2rem; }
        
        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-3 { margin-top: 0.75rem; }
        .mt-4 { margin-top: 1rem; }
        .mt-6 { margin-top: 1.5rem; }
        .mt-8 { margin-top: 2rem; }

        .hidden { display: none; }
        .block { display: block; }
        .flex { display: flex; }
        .grid { display: grid; }

        /* Mobile Optimizations */
        @media (max-width: 640px) {
            .header-content {
                padding: 0.75rem 1rem;
            }
            
            .main-content {
                padding: 0 1rem;
                padding-top: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
            
            .stat-card {
                padding: 1rem;
            }
            
            .stat-value {
                font-size: 1.25rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.875rem;
            }
            
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
            }
        }

        /* Desktop Styles */
        @media (min-width: 768px) {
            .app-container {
                flex-direction: row;
            }
            
            .main-content {
                flex: 1;
                padding-bottom: 0;
            }
            
            .bottom-nav {
                display: none;
            }
            
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            
            .header {
                position: static;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <a href="{{ route('landing') }}" class="logo">
                    <i class="fas fa-wallet"></i>
                    <span>Budget</span>
                </a>
                
                @auth
                <div class="header-actions">
                    <button class="profile-btn" onclick="toggleProfileMenu()">
                        <i class="fas fa-user"></i>
                    </button>
                </div>
                @else
                <div class="header-actions">
                    <a href="{{ route('login') }}" class="btn btn-sm btn-primary">Login</a>
                </div>
                @endauth
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="container">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>

        <!-- Bottom Navigation (Mobile) -->
        @auth
        <nav class="bottom-nav">
            <div class="nav-grid">
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="{{ route('transactions.index') }}" class="nav-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Transactions</span>
                </a>
                <a href="{{ route('budget.index') }}" class="nav-item {{ request()->routeIs('budget.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie"></i>
                    <span>Budget</span>
                </a>
                <a href="{{ route('bills.index') }}" class="nav-item {{ request()->routeIs('bills.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice"></i>
                    <span>Bills</span>
                </a>
                <a href="{{ route('savings.index') }}" class="nav-item {{ request()->routeIs('savings.*') ? 'active' : '' }}">
                    <i class="fas fa-piggy-bank"></i>
                    <span>Savings</span>
                </a>
            </div>
        </nav>
        @endauth
    </div>

    <!-- Profile Menu (Hidden by default) -->
    @auth
    <div id="profileMenu" class="hidden" style="position: fixed; top: 60px; right: 1rem; background: white; border-radius: 12px; box-shadow: var(--shadow-lg); z-index: 100; min-width: 200px;">
        <div style="padding: 1rem;">
            <div class="mb-4">
                <div class="font-semibold text-gray-800">{{ auth()->user()->full_name }}</div>
                <div class="text-sm text-gray-600">{{ auth()->user()->username }}</div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <a href="{{ route('settings.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
                @if(auth()->user()->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-shield-alt"></i> Admin
                </a>
                @endif
                <a href="{{ route('logout') }}" class="btn btn-sm btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
    @endauth

    <script>
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            menu.classList.toggle('hidden');
        }

        // Close profile menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('profileMenu');
            const profileBtn = document.querySelector('.profile-btn');
            
            if (menu && !menu.contains(event.target) && !profileBtn.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>