<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Appraisal Model
 *
 * Represents a probation review or performance appraisal for a staff member.
 * Can be used for:
 * - Probation reviews (3 month, 6 month end of probation)
 * - Annual performance appraisals
 * - Mid-year reviews
 *
 * Example:
 *   Staff: Naomi Nosa
 *   Type: Probation Review (3 months)
 *   Due: 2026-09-01
 *   Rating: Satisfactory
 *   Status: Completed
 */
class Appraisal extends Model
{
    use HasFactory;

    protected $table = 'appraisals';

    protected $fillable = [
        // Who this appraisal is for
        'staff_profile_id',

        // Type of appraisal
        'appraisal_type',   // probation_3month, probation_6month, annual, mid_year, pip

        // Dates
        'due_date',         // When the appraisal should happen
        'completed_date',   // When it was actually completed

        // Who conducts the appraisal
        'reviewer_name',    // Line manager or HR officer
        'reviewer_role',    // e.g. "Line Manager", "HR Manager"

        // Appraisal outcome
        'overall_rating',   // excellent, good, satisfactory, needs_improvement, unsatisfactory
        'status',           // pending, in_progress, completed, cancelled

        // Probation outcome (only relevant for probation types)
        'probation_outcome', // confirmed, extended, terminated

        // Evaluation sections — text areas for HR to fill in
        'performance_summary',   // Overall performance summary
        'strengths',             // What the employee does well
        'areas_for_improvement', // Areas needing development
        'goals_next_period',     // Goals set for next review period
        'employee_comments',     // Employee's own comments on the review
        'reviewer_comments',     // Reviewer's final comments

        // Reminder tracking
        'reminder_sent_at',      // When the last reminder was sent
    ];

    protected $casts = [
        'due_date'          => 'date',
        'completed_date'    => 'date',
        'reminder_sent_at'  => 'datetime',
    ];

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    /**
     * Each appraisal belongs to one staff member.
     */
    public function staffProfile()
    {
        return $this->belongsTo(StaffProfile::class);
    }

    // =========================================================
    // COMPUTED ATTRIBUTES
    // =========================================================

    /**
     * Human-readable label for the appraisal type.
     */
    public function getAppraisalTypeLabelAttribute()
    {
        return match($this->appraisal_type) {
            'probation_3month' => 'Probation Review (3 Months)',
            'probation_6month' => 'Probation Review (6 Months)',
            'annual'           => 'Annual Appraisal',
            'mid_year'         => 'Mid-Year Review',
            'pip'              => 'Performance Improvement Plan',
            default            => ucfirst(str_replace('_', ' ', $this->appraisal_type)),
        };
    }

    /**
     * Whether this is a probation-type appraisal.
     * Used to show/hide the probation outcome field.
     */
    public function getIsProbationAttribute()
    {
        return in_array($this->appraisal_type, ['probation_3month', 'probation_6month']);
    }

    /**
     * Number of days until the due date.
     * Negative if overdue.
     */
    public function getDaysUntilDueAttribute()
    {
        if (!$this->due_date) return null;
        return now()->startOfDay()->diffInDays($this->due_date, false);
    }

    /**
     * Whether the appraisal is overdue (due date passed, not completed).
     */
    public function getIsOverdueAttribute()
    {
        return $this->status !== 'completed'
            && $this->due_date
            && $this->due_date->isPast();
    }

    /**
     * Colour class for the overall rating badge.
     */
    public function getRatingColorAttribute()
    {
        return match($this->overall_rating) {
            'excellent'          => 'badge-success',
            'good'               => 'badge-info',
            'satisfactory'       => 'badge-warning',
            'needs_improvement'  => 'badge-danger',
            'unsatisfactory'     => 'badge-danger',
            default              => 'badge-secondary',
        };
    }

    /**
     * Colour class for the status badge.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'completed'   => 'badge-success',
            'in_progress' => 'badge-info',
            'pending'     => 'badge-warning',
            'cancelled'   => 'badge-secondary',
            default       => 'badge-secondary',
        };
    }

    // =========================================================
    // SCOPES
    // =========================================================

    /** Only pending appraisals */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /** Appraisals due within the next N days */
    public function scopeDueSoon($query, $days = 14)
    {
        return $query->whereIn('status', ['pending', 'in_progress'])
            ->whereDate('due_date', '>=', now())
            ->whereDate('due_date', '<=', now()->addDays($days));
    }

    /** Overdue appraisals */
    public function scopeOverdue($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress'])
            ->whereDate('due_date', '<', now());
    }
}