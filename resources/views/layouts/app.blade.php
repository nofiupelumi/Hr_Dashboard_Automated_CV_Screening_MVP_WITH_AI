<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'HR Recruitment Dashboard') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="min-h-screen">

        {{-- 
            ADMIN LAYOUT — Sidebar + Main Content
            Only shown to users with admin/HR privileges.
            All new modules should be added to the sidebar <ul> below.
        --}}
        @auth
            @if(Auth::user()->hasAdminPrivileges())
                <div class="flex">

                    <!-- ================================================
                        SIDEBAR NAVIGATION
                        Add new module links here as you build them.
                        Each <li> follows the same pattern:
                        - route() points to the module's index page
                        - routeIs() highlights the link when on that page
                    ================================================ -->
                    <nav class="sidebar w-64 flex-shrink-0">
                        <div class="sticky top-0 p-6">

                            {{-- Logo / Brand --}}
                            <div class="text-center mb-8">
                                <h2 class="text-xl font-bold text-white">HR Dashboard</h2>
                                <p class="text-primary-200 text-sm">Risk Control Services Nigeria</p>
                            </div>

                            <ul class="space-y-2">

                                {{-- Dashboard --}}
                                <li>
                                    <a href="{{ route('admin.dashboard') }}"
                                       class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                        <i class="fas fa-tachometer-alt mr-3"></i>
                                        Dashboard
                                    </a>
                                </li>

                                {{-- Job Positions (Keyword Sets) --}}
                                <li>
                                    <a href="{{ route('admin.keyword-sets.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.keyword-sets.*') ? 'active' : '' }}">
                                        <i class="fas fa-tags mr-3"></i>
                                        Job Positions
                                    </a>
                                </li>

                                {{-- CV Applications --}}
                                <li>
                                    <a href="{{ route('admin.applications.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}">
                                        <i class="fas fa-file-alt mr-3"></i>
                                        Applications
                                    </a>
                                </li>

                                {{-- MODULE 1: Staff Profiles --}}
                                <li>
                                    <a href="{{ route('admin.staff.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                                        <i class="fas fa-users mr-3"></i>
                                        Staff Profiles
                                    </a>
                                </li>

                                {{-- MODULE 2: Annual Leave Tracking --}}
                                <li>
                                    <a href="{{ route('admin.leave.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.leave.*') ? 'active' : '' }}">
                                        <i class="fas fa-calendar-alt mr-3"></i>
                                        Annual Leave
                                    </a>
                                </li>

                                {{-- 
                                    FUTURE MODULES — Uncomment as you build them:

                                <li>
                                    <a href="{{ route('admin.compliance.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.compliance.*') ? 'active' : '' }}">
                                        <i class="fas fa-shield-alt mr-3"></i>
                                        Compliance
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('admin.appraisals.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.appraisals.*') ? 'active' : '' }}">
                                        <i class="fas fa-star mr-3"></i>
                                        Appraisals
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('admin.kpis.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.kpis.*') ? 'active' : '' }}">
                                        <i class="fas fa-chart-line mr-3"></i>
                                        KPI Tracking
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('admin.attendance.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                                        <i class="fas fa-user-clock mr-3"></i>
                                        Absenteeism
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('admin.exit-reports.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.exit-reports.*') ? 'active' : '' }}">
                                        <i class="fas fa-door-open mr-3"></i>
                                        Exit Reports
                                    </a>
                                </li>
                                --}}

                                {{-- Divider before bottom links --}}
                                <li class="pt-4 border-t border-primary-700 mt-4">
                                    {{-- View the public application form --}}
                                    <a href="{{ url('/') }}" target="_blank" class="nav-link">
                                        <i class="fas fa-external-link-alt mr-3"></i>
                                        View Application Form
                                    </a>
                                </li>

                                {{-- Logout --}}
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="nav-link w-full text-left">
                                            <i class="fas fa-sign-out-alt mr-3"></i>
                                            Logout
                                        </button>
                                    </form>
                                </li>

                            </ul>
                        </div>
                    </nav>
                    <!-- END SIDEBAR -->

                    <!-- Main Content Area -->
                    <main class="flex-1 overflow-auto">
            @endif
        @endauth

        {{-- Guest layout — no sidebar, full width --}}
        @guest
            <main class="w-full">
        @endguest

                {{-- Flash Messages — shown at the top of every page --}}
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

                    {{-- Each page's content is injected here --}}
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