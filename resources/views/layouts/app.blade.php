<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'HR Recruitment Dashboard') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased">
<div class="min-h-screen">

    @auth
        @if(Auth::user()->hasAdminPrivileges())
        <div class="flex">
            <nav class="sidebar w-64 flex-shrink-0">
                <div class="sticky top-0 p-6 overflow-y-auto" style="max-height:100vh;">
                    <div class="text-center mb-8">
                        <h2 class="text-xl font-bold text-white">HR Dashboard</h2>
                        <p class="text-primary-200 text-sm">Risk Control Services Nigeria</p>
                    </div>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('admin.dashboard') }}"
                               class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.keyword-sets.index') }}"
                               class="nav-link {{ request()->routeIs('admin.keyword-sets.*') ? 'active' : '' }}">
                                <i class="fas fa-tags mr-3"></i> Job Positions
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.applications.index') }}"
                               class="nav-link {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}">
                                <i class="fas fa-file-alt mr-3"></i> Applications
                            </a>
                        </li>
                        <!-- MODULE 1: Staff Profiles -->
                        <li>
                            <a href="{{ route('admin.staff.index') }}"
                               class="nav-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                                <i class="fas fa-users mr-3"></i> Staff Profiles
                            </a>
                        </li>
                        <!-- MODULE 2: Annual Leave -->
                        <li>
                            <a href="{{ route('admin.leave.index') }}"
                               class="nav-link {{ request()->routeIs('admin.leave.*') ? 'active' : '' }}">
                                <i class="fas fa-calendar-alt mr-3"></i> Annual Leave
                            </a>
                        </li>
                        <!-- MODULE 3: KPI Tracking -->
                        <li>
                            <a href="{{ route('admin.kpis.index') }}"
                               class="nav-link {{ request()->routeIs('admin.kpis.*') ? 'active' : '' }}">
                                <i class="fas fa-chart-line mr-3"></i> KPI Tracking
                            </a>
                        </li>
                        <!-- MODULE 4: Compliance -->
                        <li>
                            <a href="{{ route('admin.compliance.index') }}"
                               class="nav-link {{ request()->routeIs('admin.compliance.*') ? 'active' : '' }}">
                                <i class="fas fa-shield-alt mr-3"></i> Compliance
                            </a>
                        </li>
                        <!-- MODULE 5: Appraisals -->
                        <li>
                            <a href="{{ route('admin.appraisals.index') }}"
                               class="nav-link {{ request()->routeIs('admin.appraisals.*') ? 'active' : '' }}">
                                <i class="fas fa-clipboard-list mr-3"></i> Appraisals
                            </a>
                        </li>
                        <!-- MODULE 6: Absenteeism -->
                        <li>
                            <a href="{{ route('admin.attendance.index') }}"
                               class="nav-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                                <i class="fas fa-user-clock mr-3"></i> Absenteeism
                            </a>
                        </li>
                        {{--
                        <!-- MODULE 7: Exit Reports — uncomment when built -->
                        <li>
                            <a href="{{ route('admin.exit-reports.index') }}"
                               class="nav-link {{ request()->routeIs('admin.exit-reports.*') ? 'active' : '' }}">
                                <i class="fas fa-door-open mr-3"></i> Exit Reports
                            </a>
                        </li>
                        --}}
                        <li class="pt-4 border-t border-primary-700 mt-4">
                            <a href="{{ url('/') }}" target="_blank" class="nav-link">
                                <i class="fas fa-external-link-alt mr-3"></i> View Application Form
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-link w-full text-left">
                                    <i class="fas fa-sign-out-alt mr-3"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>
            <main class="flex-1 overflow-auto">
        @endif
    @endauth

    @guest
        <main class="w-full">
    @endguest

            <div class="p-6">
                @if (session('success'))
                    <div class="alert alert-success mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            {{ session('error') }}
                        </div>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>

    @auth
        @if(Auth::user()->hasAdminPrivileges())
            </div>
        @endif
    @endauth

</div>
@stack('scripts')
</body>
</html>