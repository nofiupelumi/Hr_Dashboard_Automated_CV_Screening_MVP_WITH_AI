<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\StaffProfile;
use Illuminate\Http\Request;

/**
 * LeaveRequestController
 *
 * Handles all leave request operations:
 * - HR can view all requests, approve/reject, and submit on behalf of staff
 * - Staff can submit their own requests (submitted_by = 'staff')
 *
 * Routes registered in web.php as:
 *   Route::resource('leave', LeaveRequestController::class)
 *
 * Plus extra routes for approve/reject actions.
 */
class LeaveRequestController extends Controller
{
    /**
     * INDEX — List all leave requests with filters.
     * URL: GET /admin/leave
     */
    public function index(Request $request)
    {
        $query = LeaveRequest::with('staffProfile');

        // Filter by status (pending / approved / rejected)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by leave type
        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        // Search by staff name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('staffProfile', function ($q) use ($search) {
                $q->where('full_name', 'like', "%$search%");
            });
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->where('start_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('end_date', '<=', $request->to);
        }

        $leaves = $query->latest()->paginate(15)->withQueryString();

        // Summary stats for the top cards
        $stats = [
            'total'    => LeaveRequest::count(),
            'pending'  => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.leave.index', compact('leaves', 'stats'));
    }

    /**
     * CREATE — Show form to submit a new leave request.
     * HR can submit on behalf of any staff member.
     * URL: GET /admin/leave/create
     */
    public function create()
    {
        // Load all active staff for the dropdown
        $staff = StaffProfile::where('status', 'active')->orderBy('full_name')->get();
        return view('admin.leave.create', compact('staff'));
    }

    /**
     * STORE — Save a new leave request.
     * URL: POST /admin/leave
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'leave_type'       => 'required|in:annual,sick,casual,maternity,paternity,unpaid',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'reason'           => 'nullable|string|max:1000',
            'approver_type'    => 'required|in:line_manager,hr',
            'approver_name'    => 'required|string|max:255',
        ]);

        // Auto-calculate working days between start and end date
        $validated['total_days'] = LeaveRequest::calculateWorkingDays(
            $validated['start_date'],
            $validated['end_date']
        );

        // Mark as submitted by HR since this goes through the admin panel
        $validated['status']       = 'pending';
        $validated['submitted_by'] = 'hr';

        LeaveRequest::create($validated);

        return redirect()->route('admin.leave.index')
            ->with('success', 'Leave request submitted successfully!');
    }

    /**
     * SHOW — View a single leave request in detail.
     * URL: GET /admin/leave/{id}
     */
    public function show(LeaveRequest $leave)
    {
        $leave->load('staffProfile');
        return view('admin.leave.show', compact('leave'));
    }

    /**
     * EDIT — Show edit form for a leave request.
     * Only pending requests should be editable.
     * URL: GET /admin/leave/{id}/edit
     */
    public function edit(LeaveRequest $leave)
    {
        // Prevent editing already actioned requests
        if ($leave->status !== 'pending') {
            return redirect()->route('admin.leave.show', $leave)
                ->with('error', 'Only pending requests can be edited.');
        }

        $staff = StaffProfile::where('status', 'active')->orderBy('full_name')->get();
        return view('admin.leave.edit', compact('leave', 'staff'));
    }

    /**
     * UPDATE — Save edits to a leave request.
     * URL: PUT /admin/leave/{id}
     */
    public function update(Request $request, LeaveRequest $leave)
    {
        $validated = $request->validate([
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'leave_type'       => 'required|in:annual,sick,casual,maternity,paternity,unpaid',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'reason'           => 'nullable|string|max:1000',
            'approver_type'    => 'required|in:line_manager,hr',
            'approver_name'    => 'required|string|max:255',
        ]);

        // Recalculate working days if dates changed
        $validated['total_days'] = LeaveRequest::calculateWorkingDays(
            $validated['start_date'],
            $validated['end_date']
        );

        $leave->update($validated);

        return redirect()->route('admin.leave.show', $leave)
            ->with('success', 'Leave request updated successfully!');
    }

    /**
     * DESTROY — Delete a leave request.
     * URL: DELETE /admin/leave/{id}
     */
    public function destroy(LeaveRequest $leave)
    {
        $leave->delete();
        return redirect()->route('admin.leave.index')
            ->with('success', 'Leave request deleted.');
    }

    /**
     * APPROVE — Approve a pending leave request.
     * URL: POST /admin/leave/{id}/approve
     */
    public function approve(Request $request, LeaveRequest $leave)
    {
        $request->validate([
            'approved_by' => 'required|string|max:255',
        ]);

        $leave->update([
            'status'      => 'approved',
            'approved_by' => $request->approved_by,
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.leave.show', $leave)
            ->with('success', 'Leave request approved successfully!');
    }

    /**
     * REJECT — Reject a pending leave request with a reason.
     * URL: POST /admin/leave/{id}/reject
     */
    public function reject(Request $request, LeaveRequest $leave)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
            'approved_by'      => 'required|string|max:255',
        ]);

        $leave->update([
            'status'           => 'rejected',
            'approved_by'      => $request->approved_by,
            'approved_at'      => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->route('admin.leave.show', $leave)
            ->with('success', 'Leave request rejected.');
    }
}