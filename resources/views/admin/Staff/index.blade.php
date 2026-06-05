{{-- 
    resources/views/admin/staff/index.blade.php
    
    Staff Profiles — List Page
    Shows all employees in a searchable, filterable table.
    Includes stat cards at the top showing totals.
--}}
@extends('layouts.app')

@section('content')

{{-- Page Header --}}
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Staff Profiles</h1>
        <p class="text-gray-500 mt-1">Central employee database</p>
    </div>
    {{-- Button to add a new staff member manually --}}
    <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
        <i class="fas fa-plus mr-2"></i> Add Staff Member
    </a>
</div>

{{-- =====================================================
    STAT CARDS — Quick summary numbers at the top
    Data comes from $stats array in StaffProfileController@index
===================================================== --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

    <div class="bg-white p-5 rounded-lg shadow flex items-center gap-4">
        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
            <i class="fas fa-users text-xl"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
            <p class="text-gray-500 text-sm">Total Staff</p>
        </div>
    </div>

    <div class="bg-white p-5 rounded-lg shadow flex items-center gap-4">
        <div class="p-3 rounded-full bg-green-100 text-green-600">
            <i class="fas fa-user-check text-xl"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['active'] }}</p>
            <p class="text-gray-500 text-sm">Active</p>
        </div>
    </div>

    <div class="bg-white p-5 rounded-lg shadow flex items-center gap-4">
        <div class="p-3 rounded-full bg-red-100 text-red-600">
            <i class="fas fa-user-times text-xl"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['inactive'] }}</p>
            <p class="text-gray-500 text-sm">Inactive</p>
        </div>
    </div>

    <div class="bg-white p-5 rounded-lg shadow flex items-center gap-4">
        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
            <i class="fas fa-user-plus text-xl"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['this_month'] }}</p>
            <p class="text-gray-500 text-sm">Added This Month</p>
        </div>
    </div>

</div>

{{-- =====================================================
    SEARCH & FILTER BAR
    Submits as GET so filters stay in the URL
===================================================== --}}
<div class="bg-white rounded-lg shadow mb-6 p-4">
    <form method="GET" action="{{ route('admin.staff.index') }}" class="flex flex-wrap gap-3 items-end">

        {{-- Text search --}}
        <div class="flex-1 min-w-48">
            <label class="form-label">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Name, email, ID, title…" class="form-input">
        </div>

        {{-- Department filter — populated from distinct departments in the database --}}
        <div class="min-w-40">
            <label class="form-label">Department</label>
            <select name="department" class="form-select">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                        {{ $dept }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Status filter --}}
        <div class="min-w-36">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active"     {{ request('status') == 'active'     ? 'selected' : '' }}>Active</option>
                <option value="inactive"   {{ request('status') == 'inactive'   ? 'selected' : '' }}>Inactive</option>
                <option value="suspended"  {{ request('status') == 'suspended'  ? 'selected' : '' }}>Suspended</option>
                <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search mr-2"></i> Search
        </button>

        {{-- Show "Clear" button only if filters are active --}}
        @if(request()->hasAny(['search', 'department', 'status']))
            <a href="{{ route('admin.staff.index') }}" class="btn btn-outline">Clear</a>
        @endif

    </form>
</div>

{{-- =====================================================
    STAFF TABLE
===================================================== --}}
<div class="bg-white rounded-lg shadow overflow-hidden">

    @if($staff->count())

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hired</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($staff as $member)
                    <tr class="hover:bg-gray-50">

                        {{-- Employee name + email + avatar --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                {{-- Show photo if exists, otherwise show initials avatar --}}
                                @if($member->profile_photo)
                                    <img src="{{ Storage::url($member->profile_photo) }}"
                                         class="w-9 h-9 rounded-full object-cover">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-700
                                                flex items-center justify-center font-semibold text-sm">
                                        {{ $member->initials }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">{{ $member->full_name }}</p>
                                    <p class="text-gray-500 text-xs">{{ $member->email }}</p>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-mono">{{ $member->employee_id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $member->department ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $member->job_title ?? '—' }}</td>

                        {{-- Employment type badge --}}
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="badge badge-secondary">{{ $member->employment_type_label }}</span>
                        </td>

                        {{-- Status badge — colour changes based on status --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($member->status === 'active')
                                <span class="badge badge-success">Active</span>
                            @elseif($member->status === 'inactive')
                                <span class="badge badge-secondary">Inactive</span>
                            @elseif($member->status === 'suspended')
                                <span class="badge badge-warning">Suspended</span>
                            @else
                                <span class="badge badge-danger">Terminated</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $member->date_of_hire ? $member->date_of_hire->format('M d, Y') : '—' }}
                        </td>

                        {{-- Action buttons: View, Edit, Delete --}}
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.staff.show', $member) }}"
                                   class="text-blue-600 hover:text-blue-800" title="View Profile">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.staff.edit', $member) }}"
                                   class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                {{-- Delete requires a POST form because HTML forms don't support DELETE --}}
                                <form method="POST" action="{{ route('admin.staff.destroy', $member) }}"
                                      onsubmit="return confirm('Delete {{ $member->full_name }}? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
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

        {{-- Pagination links --}}
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $staff->links() }}
        </div>

    @else
        {{-- Empty state — shown when no staff match the filters --}}
        <div class="text-center py-16">
            <i class="fas fa-users text-gray-300 text-5xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No staff profiles yet</h3>
            <p class="text-gray-500 mb-4">Add your first employee to get started.</p>
            <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> Add Staff Member
            </a>
        </div>
    @endif

</div>

@endsection