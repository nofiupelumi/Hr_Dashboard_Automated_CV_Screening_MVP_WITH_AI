<?php

// namespace App\Http\Controllers;
namespace App\Http\Controllers\Admin;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

/**
 * StaffPortalController
 *
 * This is the "/my" area used by regular staff (non admin / non HR users).
 * A staff user can ONLY ever see and act on their own staff_profile's
 * Leave Requests and Appraisals — never anyone else's, and never the
 * full HR admin area.
 *
 * HR/admin keep using the existing /admin routes which already cover
 * every staff member's records.
 */
class StaffPortalController extends Controller
{
    /**
     * Resolve the StaffProfile linked to the logged in user.
     * Aborts with a friendly message if no staff record has been linked yet
     * (HR needs to set staff_profiles.user_id for this account).
     */
    private function staffProfileOrFail()
    {
        $staffProfile = auth()->user()->staffProfile;

        abort_if(
            !$staffProfile,
            403,
            'Your account is not yet linked to a staff record. Please contact HR.'
        );

        return $staffProfile;
    }

    /**
     * Simple landing page for staff — quick summary of their own
     * leave balance/status and appraisal status.
     */
    public function dashboard()
    {
        $staffProfile = $this->staffProfileOrFail();

        $leaveStats = [
            'pending'  => $staffProfile->leaveRequests()->where('status', 'pending')->count(),
            'approved' => $staffProfile->leaveRequests()->where('status', 'approved')->count(),
            'rejected' => $staffProfile->leaveRequests()->where('status', 'rejected')->count(),
        ];

        $latestAppraisal = $staffProfile->appraisals()->latest('due_date')->first();

        return view('my.dashboard', compact('staffProfile', 'leaveStats', 'latestAppraisal'));
    }

    // =========================================================
    // MY LEAVE
    // =========================================================

    /** List only the logged in staff member's own leave requests. */
    public function leaveIndex()
    {
        $staffProfile = $this->staffProfileOrFail();

        $leaves = $staffProfile->leaveRequests()->latest()->paginate(10);

        $stats = [
            'total'    => $staffProfile->leaveRequests()->count(),
            'pending'  => $staffProfile->leaveRequests()->where('status', 'pending')->count(),
            'approved' => $staffProfile->leaveRequests()->where('status', 'approved')->count(),
            'rejected' => $staffProfile->leaveRequests()->where('status', 'rejected')->count(),
        ];

        return view('my.leave.index', compact('leaves', 'stats'));
    }

    /** Show the form for the staff member to request their own leave. */
    public function leaveCreate()
    {
        $this->staffProfileOrFail();
        return view('my.leave.create');
    }

    /**
     * Staff submits their own leave request.
     * Note: status is always forced to 'pending' and submitted_by to
     * 'staff' here — a staff member can never approve their own leave.
     */
    public function leaveStore(Request $request)
    {
        $staffProfile = $this->staffProfileOrFail();

        $validated = $request->validate([
            'leave_type'    => 'required|in:annual,sick,casual,maternity,paternity,unpaid',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'nullable|string|max:1000',
            'approver_type' => 'required|in:line_manager,hr',
            'approver_name' => 'required|string|max:255',
        ]);

        $validated['staff_profile_id'] = $staffProfile->id;
        $validated['total_days'] = LeaveRequest::calculateWorkingDays(
            $validated['start_date'],
            $validated['end_date']
        );
        $validated['status']       = 'pending';
        $validated['submitted_by'] = 'staff';

        LeaveRequest::create($validated);

        return redirect()->route('my.leave.index')
            ->with('success', 'Your leave request has been submitted and is awaiting approval.');
    }

    /** View a single leave request — only if it belongs to this staff member. */
    public function leaveShow(LeaveRequest $leave)
    {
        $staffProfile = $this->staffProfileOrFail();

        abort_unless($leave->staff_profile_id === $staffProfile->id, 403);

        return view('my.leave.show', compact('leave'));
    }

    // =========================================================
    // MY APPRAISALS
    // =========================================================

    /** List only the logged in staff member's own appraisals/probation reviews. */
    public function appraisalIndex()
    {
        $staffProfile = $this->staffProfileOrFail();

        $appraisals = $staffProfile->appraisals()->latest('due_date')->paginate(10);

        return view('my.appraisals.index', compact('appraisals'));
    }

    /** View a single appraisal — only if it belongs to this staff member. */
    public function appraisalShow(\App\Models\Appraisal $appraisal)
    {
        $staffProfile = $this->staffProfileOrFail();

        abort_unless($appraisal->staff_profile_id === $staffProfile->id, 403);

        return view('my.appraisals.show', compact('appraisal'));
    }
}