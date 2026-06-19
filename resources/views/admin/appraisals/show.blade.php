{{--
    resources/views/admin/appraisals/show.blade.php
    Full appraisal detail page.
--}}
@extends('layouts.app')

@section('content')

<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.appraisals.index') }}" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Appraisal Details</h1>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('admin.appraisals.edit', $appraisal) }}" class="btn btn-primary">
            <i class="fas fa-edit mr-2"></i> Edit
        </a>
        <form method="POST" action="{{ route('admin.appraisals.destroy', $appraisal) }}"
              onsubmit="return confirm('Delete this appraisal?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash mr-2"></i> Delete
            </button>
        </form>
    </div>
</div>

{{-- Status Banner --}}
<div class="mb-6 p-4 rounded-lg border
    {{ $appraisal->is_overdue ? 'bg-red-50 border-red-200' :
       ($appraisal->status === 'completed' ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200') }}">
    <div class="flex items-center gap-3">
        <span class="badge {{ $appraisal->status_color }} text-sm px-3 py-1">
            {{ ucfirst(str_replace('_', ' ', $appraisal->status)) }}
        </span>
        <span class="text-sm text-gray-600">
            @if($appraisal->is_overdue)
                ⚠️ This appraisal is <strong>{{ abs($appraisal->days_until_due) }} day(s) overdue</strong>.
            @elseif($appraisal->status === 'completed')
                Completed on {{ $appraisal->completed_date?->format('M d, Y') }}.
            @else
                Due on <strong>{{ $appraisal->due_date->format('M d, Y') }}</strong>
                ({{ $appraisal->days_until_due }} days away).
            @endif
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    {{-- Staff & Appraisal Info --}}
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="font-semibold text-gray-900">
                <i class="fas fa-user mr-2 text-primary-600"></i>Staff & Appraisal Info
            </h3>
        </div>
        <div class="p-6">
            {{-- Staff member card --}}
            <div class="flex items-center gap-4 mb-4 pb-4 border-b border-gray-100">
                <div class="w-12 h-12 rounded-full bg-primary-100 text-primary-700
                            flex items-center justify-center font-bold">
                    {{ $appraisal->staffProfile->initials ?? '?' }}
                </div>
                <div>
                    <p class="font-semibold text-gray-900">{{ $appraisal->staffProfile->full_name }}</p>
                    <p class="text-sm text-gray-500">{{ $appraisal->staffProfile->job_title ?? 'No title' }}</p>
                    <a href="{{ route('admin.staff.show', $appraisal->staffProfile) }}"
                       class="text-blue-600 hover:underline text-xs">View Profile →</a>
                </div>
            </div>
            {{-- Details --}}
            @php
            $info = [
                'Appraisal Type' => $appraisal->appraisal_type_label,
                'Due Date'       => $appraisal->due_date->format('M d, Y'),
                'Completed Date' => $appraisal->completed_date?->format('M d, Y'),
                'Reviewer'       => $appraisal->reviewer_name,
                'Reviewer Role'  => $appraisal->reviewer_role,
            ];
            @endphp
            <div class="space-y-3">
                @foreach($info as $label => $value)
                <div class="flex justify-between py-1 border-b border-gray-100 last:border-0">
                    <span class="text-gray-500 text-sm">{{ $label }}</span>
                    <span class="text-gray-900 text-sm font-medium">{{ $value ?? '—' }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Outcome --}}
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="font-semibold text-gray-900">
                <i class="fas fa-star mr-2 text-primary-600"></i>Outcome
            </h3>
        </div>
        <div class="p-6">
            <div class="flex items-center gap-4 mb-4">
                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">Overall Rating</p>
                    @if($appraisal->overall_rating)
                        <span class="badge {{ $appraisal->rating_color }} text-sm px-4 py-2">
                            {{ ucfirst(str_replace('_', ' ', $appraisal->overall_rating)) }}
                        </span>
                    @else
                        <span class="text-gray-400 italic">Not yet rated</span>
                    @endif
                </div>
                @if($appraisal->is_probation && $appraisal->probation_outcome)
                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">Probation Outcome</p>
                    <span class="badge {{ $appraisal->probation_outcome === 'confirmed' ? 'badge-success' : ($appraisal->probation_outcome === 'terminated' ? 'badge-danger' : 'badge-warning') }} text-sm px-4 py-2">
                        {{ ucfirst($appraisal->probation_outcome) }}
                    </span>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- Evaluation Sections --}}
@php
$sections = [
    'Performance Summary'    => $appraisal->performance_summary,
    'Strengths'              => $appraisal->strengths,
    'Areas for Improvement'  => $appraisal->areas_for_improvement,
    'Goals for Next Period'  => $appraisal->goals_next_period,
    'Employee Comments'      => $appraisal->employee_comments,
    "Reviewer's Comments"    => $appraisal->reviewer_comments,
];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    @foreach($sections as $label => $content)
    @if($content)
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="font-semibold text-gray-900 text-sm">{{ $label }}</h3>
        </div>
        <div class="p-6">
            <p class="text-gray-900 text-sm whitespace-pre-line">{{ $content }}</p>
        </div>
    </div>
    @endif
    @endforeach
</div>

{{-- Other appraisals for this staff member --}}
@if($otherAppraisals->count())
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h3 class="font-semibold text-gray-900">
            <i class="fas fa-history mr-2 text-primary-600"></i>
            Other Appraisals for {{ $appraisal->staffProfile->full_name }}
        </h3>
    </div>
    <div class="divide-y divide-gray-100">
        @foreach($otherAppraisals as $other)
        <a href="{{ route('admin.appraisals.show', $other) }}"
           class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 block">
            <div>
                <p class="text-sm font-medium text-gray-900">{{ $other->appraisal_type_label }}</p>
                <p class="text-xs text-gray-500">Due: {{ $other->due_date->format('M d, Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if($other->overall_rating)
                    <span class="badge {{ $other->rating_color }}">
                        {{ ucfirst(str_replace('_', ' ', $other->overall_rating)) }}
                    </span>
                @endif
                <span class="badge {{ $other->status_color }}">
                    {{ ucfirst(str_replace('_', ' ', $other->status)) }}
                </span>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

@endsection