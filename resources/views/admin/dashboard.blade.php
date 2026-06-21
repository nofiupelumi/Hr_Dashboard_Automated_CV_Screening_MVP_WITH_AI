{{--
    resources/views/admin/dashboard.blade.php

    Main Admin Dashboard
    Shows:
    - CV Screening stats (original)
    - HR Module quick-summary cards
    - Staff birthday alerts (next 30 days)
    - Recent applications table
    - Available positions
--}}
@extends('layouts.app')

@section('content')

{{-- Page Header --}}
<div class="mb-8 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    <div class="flex space-x-4">
        <a href="{{ route('admin.applications.index') }}" class="btn btn-secondary">
            <i class="fas fa-list mr-2"></i> View All Applications
        </a>
        <a href="{{ route('admin.keyword-sets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Add Position
        </a>
    </div>
</div>

{{-- =====================================================
    CV SCREENING STATS — Original stat cards
===================================================== --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">

    <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-file-alt text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-2xl font-bold text-gray-900">{{ $stats['total_applications'] }}</h3>
                <p class="text-gray-600">Total Applications</p>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-check-circle text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-2xl font-bold text-gray-900">{{ $stats['qualified_applications'] }}</h3>
                <p class="text-gray-600">Qualified</p>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-sky-100 text-sky-600">
                <i class="fas fa-star-half-alt text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-2xl font-bold text-gray-900">{{ $stats['fairly_qualified_applications'] }}</h3>
                <p class="text-gray-600">Fairly Qualified</p>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-clock text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-2xl font-bold text-gray-900">{{ $stats['pending_applications'] }}</h3>
                <p class="text-gray-600">Pending</p>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <i class="fas fa-chart-line text-2xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-2xl font-bold text-gray-900">{{ $stats['qualification_rate'] }}%</h3>
                <p class="text-gray-600">Qualification Rate</p>
            </div>
        </div>
    </div>

</div>

{{-- =====================================================
    HR MODULE QUICK SUMMARY CARDS
===================================================== --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">

    <a href="{{ route('admin.staff.index') }}"
       class="bg-white p-4 rounded-lg shadow hover:shadow-md transition-shadow text-center">
        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-users"></i>
        </div>
        <p class="text-xl font-bold text-gray-900">{{ $hrStats['active_staff'] }}</p>
        <p class="text-xs text-gray-500">Active Staff</p>
    </a>

    <a href="{{ route('admin.leave.index') }}?status=pending"
       class="bg-white p-4 rounded-lg shadow hover:shadow-md transition-shadow text-center">
        <div class="w-10 h-10 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <p class="text-xl font-bold text-gray-900">{{ $hrStats['pending_leave'] }}</p>
        <p class="text-xs text-gray-500">Pending Leave</p>
    </a>

    <a href="{{ route('admin.compliance.index') }}?status_filter=expiring_soon"
       class="bg-white p-4 rounded-lg shadow hover:shadow-md transition-shadow text-center
              {{ $hrStats['expiring_compliance'] > 0 ? 'border-l-4 border-yellow-400' : '' }}">
        <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <p class="text-xl font-bold text-gray-900">{{ $hrStats['expiring_compliance'] }}</p>
        <p class="text-xs text-gray-500">Expiring Docs</p>
    </a>

    <a href="{{ route('admin.compliance.index') }}?status_filter=expired"
       class="bg-white p-4 rounded-lg shadow hover:shadow-md transition-shadow text-center
              {{ $hrStats['expired_compliance'] > 0 ? 'border-l-4 border-red-400' : '' }}">
        <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-times-circle"></i>
        </div>
        <p class="text-xl font-bold text-gray-900">{{ $hrStats['expired_compliance'] }}</p>
        <p class="text-xs text-gray-500">Expired Docs</p>
    </a>

    <a href="{{ route('admin.appraisals.index') }}?status=pending"
       class="bg-white p-4 rounded-lg shadow hover:shadow-md transition-shadow text-center
              {{ $hrStats['overdue_appraisals'] > 0 ? 'border-l-4 border-red-400' : '' }}">
        <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <p class="text-xl font-bold text-gray-900">{{ $hrStats['overdue_appraisals'] }}</p>
        <p class="text-xs text-gray-500">Overdue Appraisals</p>
    </a>

    <a href="{{ route('admin.staff.index') }}"
       class="bg-white p-4 rounded-lg shadow hover:shadow-md transition-shadow text-center">
        <div class="w-10 h-10 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center mx-auto mb-2">
            <i class="fas fa-users"></i>
        </div>
        <p class="text-xl font-bold text-gray-900">{{ $hrStats['total_staff'] }}</p>
        <p class="text-xs text-gray-500">Total Staff</p>
    </a>

</div>

{{-- =====================================================
    STAFF BIRTHDAYS — Next 30 days
    Shows a celebratory card for each upcoming birthday.
    If nobody has a birthday soon, this section is hidden.
===================================================== --}}
@if($upcomingBirthdays->count() > 0)
<div class="bg-white rounded-lg shadow mb-8">
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-pink-50 to-purple-50">
        <div class="flex items-center gap-2">
            <span class="text-2xl">🎂</span>
            <h2 class="text-lg font-semibold text-gray-900">Upcoming Birthdays</h2>
            <span class="ml-auto text-sm text-gray-500">Next 30 days</span>
        </div>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach($upcomingBirthdays as $staff)
            @php
                // Calculate days until birthday this year
                $birthdayThisYear = \Carbon\Carbon::createFromDate(
                    now()->year,
                    $staff->date_of_birth->month,
                    $staff->date_of_birth->day
                );
                // If birthday already passed this year, use next year
                if ($birthdayThisYear->isPast() && !$birthdayThisYear->isToday()) {
                    $birthdayThisYear->addYear();
                }
                $daysUntil = now()->startOfDay()->diffInDays($birthdayThisYear->startOfDay());
                $isToday   = $daysUntil === 0;
            @endphp
            <a href="{{ route('admin.staff.show', $staff) }}"
               class="text-center p-4 rounded-lg border hover:shadow-md transition-all
                      {{ $isToday ? 'border-pink-300 bg-pink-50' : 'border-gray-200 hover:border-pink-200' }}">
                {{-- Avatar --}}
                @if($staff->profile_photo)
                    <img src="{{ Storage::url($staff->profile_photo) }}"
                         class="w-12 h-12 rounded-full object-cover mx-auto mb-2">
                @else
                    <div class="w-12 h-12 rounded-full mx-auto mb-2 flex items-center justify-center font-bold
                                {{ $isToday ? 'bg-pink-200 text-pink-700' : 'bg-primary-100 text-primary-700' }}">
                        {{ $staff->initials }}
                    </div>
                @endif
                <p class="text-sm font-medium text-gray-900 truncate">{{ $staff->full_name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ $staff->job_title ?? $staff->department ?? '' }}</p>
                <p class="text-xs font-medium mt-1 {{ $isToday ? 'text-pink-600' : 'text-gray-600' }}">
                    @if($isToday)
                        🎉 Today!
                    @elseif($daysUntil === 1)
                        Tomorrow
                    @else
                        In {{ $daysUntil }} days
                    @endif
                </p>
                <p class="text-xs text-gray-400">{{ $staff->date_of_birth->format('M d') }}</p>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- =====================================================
    RECENT APPLICATIONS (unchanged from original)
===================================================== --}}
@if($recentApplications->count() > 0)
<div class="bg-white rounded-lg shadow mb-8">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Recent Applications</h2>
    </div>
    <div class="overflow-y-auto h-96">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qualification Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Processing Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CV</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($recentApplications as $app)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $app->applicant_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $app->applicant_email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $app->keywordSet->job_title ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($app->qualification_status === 'qualified')
                            <span class="badge badge-success">Qualified</span>
                        @elseif($app->qualification_status === 'Fairly Qualified')
                            <span class="badge badge-info">Fairly Qualified</span>
                        @elseif($app->qualification_status === 'Not Qualified')
                            <span class="badge badge-danger">Not Qualified</span>
                        @else
                            <span class="badge badge-warning">Pending</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($app->processing_status === 'completed')
                            <span class="badge badge-success">Completed</span>
                        @elseif($app->processing_status === 'failed')
                            <span class="badge badge-danger">Failed</span>
                        @elseif($app->processing_status === 'processing')
                            <span class="badge badge-info">Processing</span>
                        @else
                            <span class="badge badge-warning">Pending</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route('admin.applications.cv.download', $app->id) }}" class="text-blue-600 hover:text-blue-800">Download</a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $app->created_at->diffForHumans() }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.applications.show', $app->id) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.applications.destroy', $app->id) }}"
                                  style="display:inline;" onsubmit="return confirm('Delete this application?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- =====================================================
    AVAILABLE POSITIONS (unchanged from original)
===================================================== --}}
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900">Available Positions</h2>
            <a href="{{ route('admin.keyword-sets.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View All →</a>
        </div>
    </div>
    <div class="p-6">
        @php
            try {
                $keywordSets = \App\Models\KeywordSet::where('is_active', true)->take(6)->get();
            } catch (\Exception $e) {
                $keywordSets = collect();
            }
        @endphp

        @if($keywordSets->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($keywordSets as $set)
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ $set->job_title }}</h3>
                    @if($set->description)
                        <p class="text-gray-600 text-sm mb-3">{{ Str::limit($set->description, 80) }}</p>
                    @endif
                    <div class="flex flex-wrap gap-1">
                        @foreach(array_slice($set->keywords ?? [], 0, 3) as $keyword)
                            <span class="badge badge-secondary">{{ $keyword }}</span>
                        @endforeach
                        @if(count($set->keywords ?? []) > 3)
                            <span class="text-gray-500 text-xs self-center">+{{ count($set->keywords) - 3 }} more</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-briefcase text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No positions available</h3>
                <p class="text-gray-500 mb-4">Create keyword sets to start screening applications.</p>
                <a href="{{ route('admin.keyword-sets.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i> Create First Position
                </a>
            </div>
        @endif
    </div>
</div>

{{-- Getting Started tip (unchanged from original) --}}
<div class="mt-8">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-600 text-xl mt-0.5"></i>
            <div class="ml-4">
                <h3 class="text-lg font-medium text-blue-900 mb-2">Getting Started</h3>
                <div class="text-blue-700 space-y-2">
                    <p>• <strong>Create keyword sets</strong> for different job positions</p>
                    <p>• <strong>Share the application URL</strong>: <code class="bg-blue-100 px-2 py-1 rounded">{{ url('/') }}</code></p>
                    <p>• <strong>Monitor applications</strong> and review qualified candidates</p>
                    <p>• <strong>Add staff date of birth</strong> in their profile to see birthday alerts here</p>
                </div>
                <div class="mt-4 flex space-x-4">
                    <a href="{{ route('admin.keyword-sets.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i> Create Position
                    </a>
                    <a href="{{ url('/') }}" target="_blank" class="btn btn-outline">
                        <i class="fas fa-external-link-alt mr-2"></i> View Application Form
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection