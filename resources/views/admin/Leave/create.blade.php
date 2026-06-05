{{--
    resources/views/admin/leave/create.blade.php

    Leave Request — Create Page
    HR submits a leave request on behalf of a staff member.
    Staff member is selected from dropdown.
    Approver is selected as either Line Manager or HR.
--}}
@extends('layouts.app')

@section('content')

<div class="mb-6 flex items-center gap-4">
    <a href="{{ route('admin.leave.index') }}" class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="text-3xl font-bold text-gray-900">New Leave Request</h1>
        <p class="text-gray-500 mt-1">Submit a leave request on behalf of a staff member</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.leave.store') }}">
    @csrf

    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-user mr-2 text-primary-600"></i>Staff Member & Leave Type
            </h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Staff member dropdown — all active staff --}}
            <div>
                <label class="form-label">Staff Member <span class="text-red-500">*</span></label>
                <select name="staff_profile_id"
                        class="form-select @error('staff_profile_id') border-red-500 @enderror" required>
                    <option value="">Select staff member</option>
                    @foreach($staff as $member)
                        <option value="{{ $member->id }}"
                            {{ old('staff_profile_id') == $member->id ? 'selected' : '' }}>
                            {{ $member->full_name }}
                            @if($member->job_title) — {{ $member->job_title }} @endif
                        </option>
                    @endforeach
                </select>
                @error('staff_profile_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Leave type dropdown --}}
            <div>
                <label class="form-label">Leave Type <span class="text-red-500">*</span></label>
                <select name="leave_type"
                        class="form-select @error('leave_type') border-red-500 @enderror" required>
                    <option value="">Select type</option>
                    @foreach([
                        'annual'    => 'Annual Leave',
                        'sick'      => 'Sick Leave',
                        'casual'    => 'Casual Leave',
                        'maternity' => 'Maternity Leave',
                        'paternity' => 'Paternity Leave',
                        'unpaid'    => 'Unpaid Leave',
                    ] as $val => $label)
                        <option value="{{ $val }}" {{ old('leave_type') == $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('leave_type')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Start Date --}}
            <div>
                <label class="form-label">Start Date <span class="text-red-500">*</span></label>
                <input type="date" name="start_date"
                       value="{{ old('start_date') }}"
                       class="form-input @error('start_date') border-red-500 @enderror" required>
                @error('start_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- End Date --}}
            <div>
                <label class="form-label">End Date <span class="text-red-500">*</span></label>
                <input type="date" name="end_date"
                       value="{{ old('end_date') }}"
                       class="form-input @error('end_date') border-red-500 @enderror" required>
                @error('end_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Reason for leave --}}
            <div class="md:col-span-2">
                <label class="form-label">Reason for Leave</label>
                <textarea name="reason" rows="3" class="form-input"
                          placeholder="Optional — reason for the leave request">{{ old('reason') }}</textarea>
            </div>

        </div>
    </div>

    {{-- ===================================================
        APPROVER SECTION
        HR selects who approves — Line Manager or HR.
        When Line Manager is selected, a text field appears
        for the manager's name.
    =================================================== --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-user-check mr-2 text-primary-600"></i>Approver
            </h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Who approves: Line Manager or HR --}}
            <div>
                <label class="form-label">Approver Type <span class="text-red-500">*</span></label>
                <select name="approver_type" id="approver_type"
                        class="form-select @error('approver_type') border-red-500 @enderror"
                        required onchange="toggleApproverName()">
                    <option value="">Select approver</option>
                    <option value="line_manager" {{ old('approver_type') == 'line_manager' ? 'selected' : '' }}>
                        Line Manager
                    </option>
                    <option value="hr" {{ old('approver_type') == 'hr' ? 'selected' : '' }}>
                        HR / Admin
                    </option>
                </select>
                @error('approver_type')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Approver name — always required --}}
            <div>
                <label class="form-label">Approver Name <span class="text-red-500">*</span></label>
                <input type="text" name="approver_name" id="approver_name"
                       value="{{ old('approver_name') }}"
                       class="form-input @error('approver_name') border-red-500 @enderror"
                       placeholder="Enter approver's full name" required>
                @error('approver_name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

        </div>
    </div>

    <div class="flex justify-end gap-4">
        <a href="{{ route('admin.leave.index') }}" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane mr-2"></i> Submit Leave Request
        </button>
    </div>

</form>

@endsection