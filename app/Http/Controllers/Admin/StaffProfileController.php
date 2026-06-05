<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffProfile;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * StaffProfileController
 * 
 * Handles all CRUD operations for employee (staff) profiles.
 * Routes are registered as a resource in web.php:
 *   Route::resource('staff', StaffProfileController::class)
 * 
 * This generates these routes automatically:
 *   GET    /admin/staff              → index()   (list all staff)
 *   GET    /admin/staff/create       → create()  (show create form)
 *   POST   /admin/staff              → store()   (save new staff)
 *   GET    /admin/staff/{id}         → show()    (view one staff)
 *   GET    /admin/staff/{id}/edit    → edit()    (show edit form)
 *   PUT    /admin/staff/{id}         → update()  (save edits)
 *   DELETE /admin/staff/{id}         → destroy() (delete staff)
 */
class StaffProfileController extends Controller
{
    /**
     * INDEX — Show the full list of staff profiles.
     * Supports filtering by search term, department, and status.
     * URL: GET /admin/staff
     */
    public function index(Request $request)
    {
        // Start with all staff, then apply filters if provided
        $query = StaffProfile::query();

        // Search by name, employee ID, email, or job title
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('full_name',   'like', "%$s%")
                  ->orWhere('employee_id', 'like', "%$s%")
                  ->orWhere('email',       'like', "%$s%")
                  ->orWhere('job_title',   'like', "%$s%");
            });
        }

        // Filter by department if selected
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        // Filter by employment status if selected
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Paginate results — 15 per page, keep filter params in pagination links
        $staff = $query->latest()->paginate(15)->withQueryString();

        // Get unique departments for the filter dropdown
        $departments = StaffProfile::distinct()->pluck('department')->filter()->sort()->values();

        // Summary counts for the stat cards at the top of the page
        $stats = [
            'total'      => StaffProfile::count(),
            'active'     => StaffProfile::where('status', 'active')->count(),
            'inactive'   => StaffProfile::where('status', '!=', 'active')->count(),
            'this_month' => StaffProfile::whereMonth('created_at', now()->month)->count(),
        ];

        return view('admin.staff.index', compact('staff', 'departments', 'stats'));
    }

    /**
     * CREATE — Show the blank form to add a new staff member.
     * Can optionally pre-fill the form from an approved CV application.
     * URL: GET /admin/staff/create?from_application=5
     */
    public function create(Request $request)
    {
        // If HR clicked "Convert to Staff" from an application, pre-fill the form
        $application = null;
        if ($request->filled('from_application')) {
            $application = Application::find($request->from_application);
        }

        // Auto-generate the next employee ID (e.g. EMP0003)
        $nextId = StaffProfile::generateEmployeeId();

        return view('admin.staff.create', compact('application', 'nextId'));
    }

    /**
     * STORE — Save a new staff profile to the database.
     * URL: POST /admin/staff
     */
    public function store(Request $request)
    {
        // Validate all form inputs before saving
        $validated = $request->validate([
            // Required fields
            'full_name'                      => 'required|string|max:255',
            'employee_id'                    => 'required|string|unique:staff_profiles,employee_id',
            'email'                          => 'required|email|unique:staff_profiles,email',

            // Personal info — all optional
            'gender'                         => 'nullable|in:male,female,other',
            'date_of_birth'                  => 'nullable|date|before:today',
            'marital_status'                 => 'nullable|in:single,married,divorced,widowed',
            'nationality'                    => 'nullable|string|max:100',
            'phone_number'                   => 'nullable|string|max:20',
            'residential_address'            => 'nullable|string',

            // Emergency contact — all optional
            'emergency_contact_name'         => 'nullable|string|max:255',
            'emergency_contact_phone'        => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',

            // Employment details — all optional
            'job_title'                      => 'nullable|string|max:255',
            'department'                     => 'nullable|string|max:100',
            'location'                       => 'nullable|string|max:255',
            'employment_type'                => 'nullable|in:full_time,part_time,contract,intern',
            'date_of_hire'                   => 'nullable|date',
            'status'                         => 'nullable|in:active,inactive,suspended,terminated',
            'line_manager'                   => 'nullable|string|max:255',
            'department_head'                => 'nullable|string|max:255',

            // Identification & compensation — all optional
            'national_id'                    => 'nullable|string|max:100',
            'tax_id'                         => 'nullable|string|max:100',
            'pension_details'                => 'nullable|string|max:255',
            'salary'                         => 'nullable|numeric|min:0',
            'bank_name'                      => 'nullable|string|max:100',
            'bank_account_number'            => 'nullable|string|max:50',

            // Education & history — all optional
            'academic_background'            => 'nullable|string',
            'certifications'                 => 'nullable|string',
            'professional_memberships'       => 'nullable|string',
            'previous_roles'                 => 'nullable|string',
            'promotion_history'              => 'nullable|string',

            // Link to original application — optional
            'application_id'                 => 'nullable|exists:applications,id',

            // Profile photo — optional, must be an image under 2MB
            'profile_photo'                  => 'nullable|image|max:2048',
        ]);

        // If a photo was uploaded, save it to storage/app/public/staff_photos/
        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] = $request->file('profile_photo')
                ->store('staff_photos', 'public');
        }

        // Create the staff profile record in the database
        StaffProfile::create($validated);

        // Redirect back to the list with a success message
        return redirect()->route('admin.staff.index')
            ->with('success', "Staff profile for {$validated['full_name']} created successfully!");
    }

    /**
     * SHOW — Display a single staff member's full profile.
     * URL: GET /admin/staff/{id}
     */
    public function show(StaffProfile $staff)
    {
        // Also load the linked application record (if any) in one query
        $staff->load('application');
        return view('admin.staff.show', compact('staff'));
    }

    /**
     * EDIT — Show the edit form pre-filled with existing data.
     * URL: GET /admin/staff/{id}/edit
     */
    public function edit(StaffProfile $staff)
    {
        return view('admin.staff.edit', compact('staff'));
    }

    /**
     * UPDATE — Save changes to an existing staff profile.
     * URL: PUT /admin/staff/{id}
     */
    public function update(Request $request, StaffProfile $staff)
    {
        // Same validation as store(), but employee_id and email
        // must be unique EXCEPT for this staff member's own record
        $validated = $request->validate([
            'full_name'                      => 'required|string|max:255',
            'employee_id'                    => 'required|string|unique:staff_profiles,employee_id,' . $staff->id,
            'email'                          => 'required|email|unique:staff_profiles,email,' . $staff->id,
            'gender'                         => 'nullable|in:male,female,other',
            'date_of_birth'                  => 'nullable|date|before:today',
            'marital_status'                 => 'nullable|in:single,married,divorced,widowed',
            'nationality'                    => 'nullable|string|max:100',
            'phone_number'                   => 'nullable|string|max:20',
            'residential_address'            => 'nullable|string',
            'emergency_contact_name'         => 'nullable|string|max:255',
            'emergency_contact_phone'        => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'job_title'                      => 'nullable|string|max:255',
            'department'                     => 'nullable|string|max:100',
            'location'                       => 'nullable|string|max:255',
            'employment_type'                => 'nullable|in:full_time,part_time,contract,intern',
            'date_of_hire'                   => 'nullable|date',
            'status'                         => 'nullable|in:active,inactive,suspended,terminated',
            'line_manager'                   => 'nullable|string|max:255',
            'department_head'                => 'nullable|string|max:255',
            'national_id'                    => 'nullable|string|max:100',
            'tax_id'                         => 'nullable|string|max:100',
            'pension_details'                => 'nullable|string|max:255',
            'salary'                         => 'nullable|numeric|min:0',
            'bank_name'                      => 'nullable|string|max:100',
            'bank_account_number'            => 'nullable|string|max:50',
            'academic_background'            => 'nullable|string',
            'certifications'                 => 'nullable|string',
            'professional_memberships'       => 'nullable|string',
            'previous_roles'                 => 'nullable|string',
            'promotion_history'              => 'nullable|string',
            'profile_photo'                  => 'nullable|image|max:2048',
        ]);

        // If a new photo was uploaded, delete the old one first, then save the new one
        if ($request->hasFile('profile_photo')) {
            if ($staff->profile_photo) {
                Storage::disk('public')->delete($staff->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')
                ->store('staff_photos', 'public');
        }

        // Update the record in the database
        $staff->update($validated);

        // Redirect to the profile view with a success message
        return redirect()->route('admin.staff.show', $staff)
            ->with('success', 'Staff profile updated successfully!');
    }

    /**
     * DESTROY — Permanently delete a staff profile.
     * URL: DELETE /admin/staff/{id}
     */
    public function destroy(StaffProfile $staff)
    {
        // Delete the profile photo from storage if one exists
        if ($staff->profile_photo) {
            Storage::disk('public')->delete($staff->profile_photo);
        }

        $name = $staff->full_name;
        $staff->delete();

        return redirect()->route('admin.staff.index')
            ->with('success', "$name has been removed from the system.");
    }
}