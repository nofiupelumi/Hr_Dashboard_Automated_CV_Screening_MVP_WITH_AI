<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * LeaveRequest Model
 *
 * Represents a leave request submitted by or on behalf of a staff member.
 * HR or the staff member can submit it.
 * It gets approved/rejected by either a Line Manager or HR.
 */
class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        // Who the leave is for
        'staff_profile_id',

        // Leave details
        'leave_type',        // annual, sick, casual, maternity, paternity, unpaid
        'start_date',
        'end_date',
        'total_days',
        'reason',

        // Who should approve it — selected from a dropdown
        'approver_type',     // 'line_manager' or 'hr'
        'approver_name',     // The actual name of the approver

        // Status tracking
        'status',            // pending, approved, rejected
        'approved_by',       // Name of person who actioned it
        'approved_at',       // When it was actioned
        'rejection_reason',  // If rejected, why

        // Was this submitted by HR on behalf of staff, or by staff themselves?
        'submitted_by',      // 'hr' or 'staff'
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'approved_at' => 'datetime',
    ];

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    /**
     * Each leave request belongs to one staff member.
     */
    public function staffProfile()
    {
        return $this->belongsTo(StaffProfile::class);
    }

    // =========================================================
    // COMPUTED ATTRIBUTES
    // =========================================================

    /**
     * Return a human-readable label for the leave type.
     * e.g. 'annual' → 'Annual Leave'
     */
    public function getLeaveTypeLabelAttribute()
    {
        return match($this->leave_type) {
            'annual'     => 'Annual Leave',
            'sick'       => 'Sick Leave',
            'casual'     => 'Casual Leave',
            'maternity'  => 'Maternity Leave',
            'paternity'  => 'Paternity Leave',
            'unpaid'     => 'Unpaid Leave',
            default      => ucfirst($this->leave_type),
        };
    }

    /**
     * Return a colour class for the status badge in the UI.
     * Used in Blade views: {{ $leave->status_color }}
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            default    => 'badge-warning', // pending
        };
    }

    // =========================================================
    // SCOPES
    // =========================================================

    /** Only pending requests */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /** Only approved requests */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /** Filter by staff member */
    public function scopeForStaff($query, $staffId)
    {
        return $query->where('staff_profile_id', $staffId);
    }

    // =========================================================
    // HELPERS
    // =========================================================

    /**
     * Calculate working days between two dates (excludes weekends).
     * Called when saving a leave request to set total_days.
     */
    public static function calculateWorkingDays($startDate, $endDate): int
    {
        $start   = \Carbon\Carbon::parse($startDate);
        $end     = \Carbon\Carbon::parse($endDate);
        $days    = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            // 1 = Monday ... 5 = Friday, skip Saturday(6) and Sunday(0)
            if ($current->dayOfWeek !== 0 && $current->dayOfWeek !== 6) {
                $days++;
            }
            $current->addDay();
        }

        return $days;
    }
}