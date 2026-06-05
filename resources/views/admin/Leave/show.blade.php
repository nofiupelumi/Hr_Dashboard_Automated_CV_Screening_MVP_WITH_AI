{{--
    resources/views/admin/leave/show.blade.php

    Leave Request — View/Detail Page
    Shows full details of a leave request.
    HR can approve or reject from this page.
--}}
@extends('layouts.app')

@section('content')

<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.leave.index') }}" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Leave Request Details</h1>
    </div>
    <div class="flex gap-3">
        @if($leave->status === 'pending')
            <a href="{{ route('admin.leave.edit', $leave) }}" class="btn btn-outline">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
        @endif
        <form method="POST" action="{{ route('admin.leave.destroy', $leave) }}"
              onsubmit="return confirm('Delete this leave request?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash mr-2"></i> Delete
            </button>
        </form>
    </div>
</div>

{{-- Status banner at the top --}}
<div class="mb-6 p-4 rounded-lg border
    {{ $leave->status === 'approved' ? 'bg-green-50 border-green-200' :
       ($leave->status === 'rejected' ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200') }}">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="badge {{ $leave->status_color }} text-sm px-3 py-1">
                {{ ucfirst($leave->status) }}
            </span>
            <span class="text-sm text-gray-600">
                @if($leave->status === 'pending')
                    Awaiting approval from <strong>{{ $leave->approver_name }}</strong>
                @else
                    {{ ucfirst($leave->status) }} by <strong>{{ $leave->approved_by }}</strong>
                    on {{ $leave->approved_at?->format('M d, Y \a\t g:i A') }}
                @endif
            </span>
        </div>
    </div>
    @if($leave->status === 'rejected' && $leave->rejection_reason)
        <p class="mt-2 text-sm text-red-700">
            <strong>Reason:</strong> {{ $leave->rejection_reason }}
        </p>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    {{-- Staff Info --}}
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="font-semibold text-gray-900">
                <i class="fas fa-user mr-2 text-primary-600"></i>Staff Member
            </h3>
        </div>
        <div class="p-6 flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-primary-100 text-primary-700
                        flex items-center justify-center text-lg font-bold">
                {{ $leave->staffProfile->initials ?? '?' }}
            </div>
            <div>
                <p class="font-semibold text-gray-900 text-lg">{{ $leave->staffProfile->full_name }}</p>
                <p class="text-gray-500 text-sm">{{ $leave->staffProfile->job_title ?? 'No title' }}</p>
                <p class="text-gray-500 text-sm">{{ $leave->staffProfile->department ?? 'No department' }}</p>
                <a href="{{ route('admin.staff.show', $leave->staffProfile) }}"
                   class="text-blue-600 hover:underline text-sm mt-1 inline-block">
                    View Profile →
                </a>
            </div>
        </div>
    </div>

    {{-- Leave Details --}}
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="font-semibold text-gray-900">
                <i class="fas fa-calendar mr-2 text-primary-600"></i>Leave Details
            </h3>
        </div>
        <div class="p-6 space-y-3">
            @php
            $details = [
                'Leave Type'   => $leave->leave_type_label,
                'Start Date'   => $leave->start_date->format('l, M d, Y'),
                'End Date'     => $leave->end_date->format('l, M d, Y'),
                'Total Days'   => $leave->total_days . ' working day(s)',
                'Approver'     => ($leave->approver_type === 'hr' ? 'HR: ' : 'Line Manager: ') . $leave->approver_name,
                'Submitted By' => ucfirst($leave->submitted_by),
                'Submitted On' => $leave->created_at->format('M d, Y \a\t g:i A'),
            ];
            @endphp
            @foreach($details as $label => $value)
            <div class="flex justify-between py-1 border-b border-gray-100 last:border-0">
                <span class="text-gray-500 text-sm">{{ $label }}</span>
                <span class="text-gray-900 text-sm font-medium">{{ $value }}</span>
            </div>
            @endforeach
            @if($leave->reason)
            <div class="pt-2">
                <p class="text-gray-500 text-sm mb-1">Reason</p>
                <p class="text-gray-900 text-sm">{{ $leave->reason }}</p>
            </div>
            @endif
        </div>
    </div>

</div>

{{-- =====================================================
    APPROVE / REJECT ACTIONS
    Only shown for pending requests
===================================================== --}}
@if($leave->status === 'pending')
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Approve Form --}}
    <div class="bg-white rounded-lg shadow border-t-4 border-green-400">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900 text-green-700">
                <i class="fas fa-check-circle mr-2"></i>Approve Request
            </h3>
        </div>
        <form method="POST" action="{{ route('admin.leave.approve', $leave) }}" class="p-6">
            @csrf
            <div class="mb-4">
                <label class="form-label">Approved By <span class="text-red-500">*</span></label>
                <input type="text" name="approved_by"
                       placeholder="Your name / HR officer name"
                       class="form-input" required>
            </div>
            <button type="submit" class="btn btn-success w-full"
                    onclick="return confirm('Approve this leave request?')">
                <i class="fas fa-check mr-2"></i> Approve Leave
            </button>
        </form>
    </div>

    {{-- Reject Form --}}
    <div class="bg-white rounded-lg shadow border-t-4 border-red-400">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900 text-red-700">
                <i class="fas fa-times-circle mr-2"></i>Reject Request
            </h3>
        </div>
        <form method="POST" action="{{ route('admin.leave.reject', $leave) }}" class="p-6">
            @csrf
            <div class="mb-4">
                <label class="form-label">Rejected By <span class="text-red-500">*</span></label>
                <input type="text" name="approved_by"
                       placeholder="Your name / HR officer name"
                       class="form-input" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Reason for Rejection <span class="text-red-500">*</span></label>
                <textarea name="rejection_reason" rows="3" class="form-input"
                          placeholder="Explain why this request is being rejected..." required></textarea>
            </div>
            <button type="submit" class="btn btn-danger w-full"
                    onclick="return confirm('Reject this leave request?')">
                <i class="fas fa-times mr-2"></i> Reject Leave
            </button>
        </form>
    </div>

</div>
@endif

@endsection