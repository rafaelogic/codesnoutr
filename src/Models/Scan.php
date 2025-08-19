<?php

namespace Rafaelogic\CodeSnoutr\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Scan extends Model
{
    protected $table = 'codesnoutr_scans';

    protected $fillable = [
        'type',
        'target',
        'status',
        'scan_options',
        'paths_scanned',
        'total_files',
        'total_issues',
        'critical_issues',
        'warning_issues',
        'info_issues',
        'started_at',
        'completed_at',
        'duration_seconds',
        'summary',
        'error_message',
        'ai_cost',
    ];

    protected $casts = [
        'scan_options' => 'array',
        'paths_scanned' => 'array',
        'summary' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'ai_cost' => 'decimal:4',
    ];

    /**
     * Get all issues for this scan
     */
    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    /**
     * Get critical issues
     */
    public function criticalIssues(): HasMany
    {
        return $this->issues()->where('severity', 'critical');
    }

    /**
     * Get warning issues
     */
    public function warningIssues(): HasMany
    {
        return $this->issues()->where('severity', 'warning');
    }

    /**
     * Get info issues
     */
    public function infoIssues(): HasMany
    {
        return $this->issues()->where('severity', 'info');
    }

    /**
     * Get issues by category
     */
    public function issuesByCategory(string $category): HasMany
    {
        return $this->issues()->where('category', $category);
    }

    /**
     * Get scan progress percentage
     */
    public function progressPercentage(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->status === 'completed') {
                    return 100;
                }
                if ($this->status === 'failed') {
                    return 0;
                }
                if ($this->status === 'running' && $this->total_files > 0) {
                    // This would need to be calculated based on files processed
                    // For now, return a placeholder
                    return 50;
                }
                return 0;
            }
        );
    }

    /**
     * Check if scan is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if scan is running
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    /**
     * Check if scan failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get severity distribution
     */
    public function severityDistribution(): array
    {
        return [
            'critical' => $this->critical_issues,
            'warning' => $this->warning_issues,
            'info' => $this->info_issues,
        ];
    }

    /**
     * Get category distribution
     */
    public function categoryDistribution(): array
    {
        return $this->issues()
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();
    }

    /**
     * Mark scan as started
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark scan as completed
     */
    public function markAsCompleted(): void
    {
        $completedAt = now();
        $duration = null;
        if ($this->started_at) {
            $duration = $this->started_at->diffInSeconds($completedAt);
        }

        $this->update([
            'status' => 'completed',
            'completed_at' => $completedAt,
            'duration_seconds' => $duration,
        ]);
    }

    /**
     * Mark scan as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $duration = null;
        if ($this->started_at && $this->completed_at) {
            $duration = $this->completed_at->diffInSeconds($this->started_at);
        } elseif ($this->started_at) {
            $duration = now()->diffInSeconds($this->started_at);
        }

        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $errorMessage,
            'duration_seconds' => $duration,
        ]);
    }

    /**
     * Update scan statistics
     */
    public function updateStatistics(): void
    {
        $issues = $this->issues();
        
        $this->update([
            'total_issues' => $issues->count(),
            'critical_issues' => $issues->where('severity', 'critical')->count(),
            'warning_issues' => $issues->where('severity', 'warning')->count(),
            'info_issues' => $issues->where('severity', 'info')->count(),
        ]);
    }

    /**
     * Scope for recent scans
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for completed scans
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed scans
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
