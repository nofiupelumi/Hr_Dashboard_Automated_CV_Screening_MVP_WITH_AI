<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appraisal;
use App\Models\StaffProfile;
use Illuminate\Http\Request;

/**
 * AppraisalController
 *
 * Manages probation reviews and performance appraisals for staff.
 * HR can:
 * - Schedule upcoming appraisals with due dates
 * - Fill in evaluation forms (strengths, improvements, goals)
 * - Record the outcome (rating, probation decision)
 * - View overdue and upcoming appraisals on the dashboard
 *
 * Routes in web.php:
 *   Route::resource('appraisals', AppraisalController::class)
 */
class AppraisalController extends Controller
{
    /**
     * INDEX — List all appraisals with filters and alerts.
     * URL: GET /admin/appraisals
     */
    public function index(Request $request)
    {
        $query = Appraisal::with('staffProfile');

        // Search by staff name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('staffProfile', function ($q) use ($search) {
                $q->where('full_name', 'like', "%$search%");
            });
        }

        // Filter by appraisal type
        if ($request->filled('appraisal_type')) {
            $query->where('appraisal_type', $request->appraisal_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Order by due date — most urgent first
        $appraisals = $query->orderBy('due_date')->paginate(15)->withQueryString();

        // Summary stats for the top cards
        $stats = [
            'total'       => Appraisal::count(),
            'pending'     => Appraisal::where('status', 'pending')->count(),
            'overdue'     => Appraisal::overdue()->count(),
            'due_soon'    => Appraisal::dueSoon()->count(),
            'completed'   => Appraisal::where('status', 'completed')->count(),
        ];

        return view('admin.appraisals.index', compact('appraisals', 'stats'));
    }

    /**
     * CREATE — Show form to schedule a new appraisal.
     * URL: GET /admin/appraisals/create
     */
    public function create()
    {
        $staff = StaffProfile::where('status', 'active')->orderBy('full_name')->get();
        return view('admin.appraisals.create', compact('staff'));
    }

    /**
     * STORE — Save a new appraisal.
     * URL: POST /admin/appraisals
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'staff_profile_id'       => 'required|exists:staff_profiles,id',
            'appraisal_type'         => 'required|in:probation_3month,probation_6month,annual,mid_year,pip',
            'due_date'               => 'required|date',
            'completed_date'         => 'nullable|date',
            'reviewer_name'          => 'required|string|max:255',
            'reviewer_role'          => 'nullable|string|max:100',
            'overall_rating'         => 'nullable|in:excellent,good,satisfactory,needs_improvement,unsatisfactory',
            'status'                 => 'required|in:pending,in_progress,completed,cancelled',
            'probation_outcome'      => 'nullable|in:confirmed,extended,terminated',
            'performance_summary'    => 'nullable|string',
            'strengths'              => 'nullable|string',
            'areas_for_improvement'  => 'nullable|string',
            'goals_next_period'      => 'nullable|string',
            'employee_comments'      => 'nullable|string',
            'reviewer_comments'      => 'nullable|string',
        ]);

        // Auto-set completed_date if status is being set to completed
        if ($validated['status'] === 'completed' && empty($validated['completed_date'])) {
            $validated['completed_date'] = now()->toDateString();
        }

        Appraisal::create($validated);

        return redirect()->route('admin.appraisals.index')
            ->with('success', 'Appraisal scheduled successfully!');
    }

    /**
     * SHOW — View a single appraisal in detail.
     * URL: GET /admin/appraisals/{id}
     */
    public function show(Appraisal $appraisal)
    {
        $appraisal->load('staffProfile');

        // Load other appraisals for the same staff member
        $otherAppraisals = Appraisal::where('staff_profile_id', $appraisal->staff_profile_id)
            ->where('id', '!=', $appraisal->id)
            ->orderBy('due_date', 'desc')
            ->take(5)
            ->get();

        return view('admin.appraisals.show', compact('appraisal', 'otherAppraisals'));
    }

    /**
     * EDIT — Show edit form pre-filled with existing data.
     * URL: GET /admin/appraisals/{id}/edit
     */
    public function edit(Appraisal $appraisal)
    {
        $staff = StaffProfile::where('status', 'active')->orderBy('full_name')->get();
        return view('admin.appraisals.edit', compact('appraisal', 'staff'));
    }

    /**
     * UPDATE — Save changes to an appraisal.
     * URL: PUT /admin/appraisals/{id}
     */
    public function update(Request $request, Appraisal $appraisal)
    {
        $validated = $request->validate([
            'staff_profile_id'       => 'required|exists:staff_profiles,id',
            'appraisal_type'         => 'required|in:probation_3month,probation_6month,annual,mid_year,pip',
            'due_date'               => 'required|date',
            'completed_date'         => 'nullable|date',
            'reviewer_name'          => 'required|string|max:255',
            'reviewer_role'          => 'nullable|string|max:100',
            'overall_rating'         => 'nullable|in:excellent,good,satisfactory,needs_improvement,unsatisfactory',
            'status'                 => 'required|in:pending,in_progress,completed,cancelled',
            'probation_outcome'      => 'nullable|in:confirmed,extended,terminated',
            'performance_summary'    => 'nullable|string',
            'strengths'              => 'nullable|string',
            'areas_for_improvement'  => 'nullable|string',
            'goals_next_period'      => 'nullable|string',
            'employee_comments'      => 'nullable|string',
            'reviewer_comments'      => 'nullable|string',
        ]);

        // Auto-set completed_date when marking as completed
        if ($validated['status'] === 'completed' && empty($validated['completed_date'])) {
            $validated['completed_date'] = now()->toDateString();
        }

        $appraisal->update($validated);

        return redirect()->route('admin.appraisals.show', $appraisal)
            ->with('success', 'Appraisal updated successfully!');
    }

    /**
     * DESTROY — Delete an appraisal.
     * URL: DELETE /admin/appraisals/{id}
     */
    public function destroy(Appraisal $appraisal)
    {
        $type = $appraisal->appraisal_type_label;
        $appraisal->delete();

        return redirect()->route('admin.appraisals.index')
            ->with('success', "'{$type}' appraisal deleted successfully.");
    }
}