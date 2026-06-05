{{--
    resources/views/admin/leave/index.blade.php
    Leave Tracking — List Page (fixed: clickable rows, no horizontal scroll)
--}}
@extends('layouts.app')

@section('content')

{{-- Page Header --}}
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Annual Leave Tracking</h1>
        <p class="text-gray-500 mt-1">Manage all staff leave requests</p>
    </div>
    <a href="{{ route('admin.leave.create') }}" class="btn btn-primary">
        <i class="fas fa-plus mr-2"></i> New Leave Request
    </a>
</div>

{{-- STAT CARDS --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

    <div class="bg-white p-5 rounded-lg shadow flex items-center gap-4">
        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
            <i class="fas fa-calendar-alt text-xl"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
            <p class="text-gray-500 text-sm">Total Requests</p>
        </div>
    </div>

    <div class="bg-white p-5 rounded-lg shadow flex items-center gap-4">
        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
            <i class="fas fa-clock text-xl"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
            <p class="text-gray-500 text-sm">Pending</p>
        </div>
    </div>

    <div class="bg-white p-5 rounded-lg shadow flex items-center gap-4">
        <div class="p-3 rounded-full bg-green-100 text-green-600">
            <i class="fas fa-check-circle text-xl"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['approved'] }}</p>
            <p class="text-gray-500 text-sm">Approved</p>
        </div>
    </div>

    <div class="bg-white p-5 rounded-lg shadow flex items-center gap-4">
        <div class="p-3 rounded-full bg-red-100 text-red-600">
            <i class="fas fa-times-circle text-xl"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['rejected'] }}</p>
            <p class="text-gray-500 text-sm">Rejected</p>
        </div>
    </div>

</div>

{{-- FILTER BAR --}}
<div class="bg-white rounded-lg shadow mb-6 p-4">
    <form method="GET" action="{{ route('admin.leave.index') }}" class="flex flex-wrap gap-3 items-end">

        <div class="flex-1 min-w-40">
            <label class="form-label">Search Staff</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Staff name..." class="form-input">
        </div>

        <div class="min-w-36">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="pending"  {{ request('status') == 'pending'  ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>

        <div class="min-w-40">
            <label class="form-label">Leave Type</label>
            <select name="leave_type" class="form-select">
                <option value="">All Types</option>
                @foreach(['annual' => 'Annual', 'sick' => 'Sick', 'casual' => 'Casual', 'maternity' => 'Maternity', 'paternity' => 'Paternity', 'unpaid' => 'Unpaid'] as $val => $label)
                    <option value="{{ $val }}" {{ request('leave_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search mr-2"></i> Filter
        </button>

        @if(request()->hasAny(['search', 'status', 'leave_type']))
            <a href="{{ route('admin.leave.index') }}" class="btn btn-outline">Clear</a>
        @endif

    </form>
</div>

{{-- LEAVE REQUESTS — Card layout instead of table to avoid cutoff --}}
<div class="space-y-4">

    @if($leaves->count())

        @foreach($leaves as $leave)
        {{-- 
            Each leave request is a clickable card.
            Clicking anywhere on the card opens the detail page.
        --}}
        <a href="{{ route('admin.leave.show', $leave) }}"
           class="block bg-white rounded-lg shadow hover:shadow-md hover:border-primary-300
                  border border-transparent transition-all duration-200 cursor-pointer">
            <div class="p-5">
                <div class="flex flex-wrap items-center justify-between gap-4">

                    {{-- Staff Member Info --}}
                    <div class="flex items-center gap-3 min-w-48">
                        <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700
                                    flex items-center justify-center font-bold text-sm flex-shrink-0">
                            {{ $leave->staffProfile->initials ?? '?' }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">
                                {{ $leave->staffProfile->full_name ?? 'Unknown' }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $leave->staffProfile->job_title ?? 'No title' }}
                            </p>
                        </div>
                    </div>

                    {{-- Leave Type --}}
                    <div class="min-w-32">
                        <p class="text-xs text-gray-400 uppercase mb-1">Leave Type</p>
                        <p class="text-sm font-medium text-gray-900">{{ $leave->leave_type_label }}</p>
                    </div>

                    {{-- Dates --}}
                    <div class="min-w-40">
                        <p class="text-xs text-gray-400 uppercase mb-1">Dates</p>
                        <p class="text-sm text-gray-900">
                            {{ $leave->start_date->format('M d, Y') }}
                        </p>
                        <p class="text-xs text-gray-500">
                            to {{ $leave->end_date->format('M d, Y') }}
                        </p>
                    </div>

                    {{-- Days --}}
                    <div class="min-w-20">
                        <p class="text-xs text-gray-400 uppercase mb-1">Days</p>
                        <p class="text-sm font-bold text-gray-900">
                            {{ $leave->total_days }} day{{ $leave->total_days > 1 ? 's' : '' }}
                        </p>
                    </div>

                    {{-- Approver --}}
                    <div class="min-w-36">
                        <p class="text-xs text-gray-400 uppercase mb-1">Approver</p>
                        <p class="text-xs text-gray-500">
                            {{ $leave->approver_type === 'hr' ? 'HR' : 'Line Manager' }}
                        </p>
                        <p class="text-sm text-gray-900">{{ $leave->approver_name }}</p>
                    </div>

                    {{-- Status + Arrow --}}
                    <div class="flex items-center gap-3">
                        <span class="badge {{ $leave->status_color }} px-3 py-1">
                            {{ ucfirst($leave->status) }}
                        </span>
                        {{-- Arrow hint that this is clickable --}}
                        <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                    </div>

                </div>

                {{-- Show rejection reason if rejected --}}
                @if($leave->status === 'rejected' && $leave->rejection_reason)
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <p class="text-xs text-red-600">
                        <i class="fas fa-times-circle mr-1"></i>
                        Rejected: {{ $leave->rejection_reason }}
                    </p>
                </div>
                @endif

                {{-- Show who approved it --}}
                @if($leave->status === 'approved' && $leave->approved_by)
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <p class="text-xs text-green-600">
                        <i class="fas fa-check-circle mr-1"></i>
                        Approved by {{ $leave->approved_by }}
                        on {{ $leave->approved_at?->format('M d, Y') }}
                    </p>
                </div>
                @endif

            </div>
        </a>
        @endforeach

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $leaves->links() }}
        </div>

    @else
        <div class="bg-white rounded-lg shadow text-center py-16">
            <i class="fas fa-calendar-times text-gray-300 text-5xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No leave requests found</h3>
            <p class="text-gray-500 mb-4">Submit the first leave request to get started.</p>
            <a href="{{ route('admin.leave.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> New Leave Request
            </a>
        </div>
    @endif

</div>

@endsection