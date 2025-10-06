<?php

namespace Rafaelogic\CodeSnoutr\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Issue extends Model
{
    use HasFactory;
    
    protected $table = 'codesnoutr_issues';

    protected $fillable = [
        'scan_id',
        'file_path',
        'line_number',
        'column_number',
        'category',
        'severity',
        'rule_name',
        'rule_id',
        'title',
        'description',
        'suggestion',
        'context',
        'metadata',
        'ai_fix',
        'ai_explanation',
        'ai_confidence',
        'fixed',
        'fixed_at',
        'fix_method',
        'skipped',
        'skipped_at',
        'skip_reason',
        'fix_attempts',
        'fix_attempt_count',
        'last_fix_attempt_at',
        'ignored',
        'ignored_at',
        'false_positive',
        'false_positive_at',
    ];

    protected $casts = [
        'context' => 'array',
        'metadata' => 'array',
        'fixed' => 'boolean',
        'skipped' => 'boolean',
        'fixed_at' => 'datetime',
        'skipped_at' => 'datetime',
        'last_fix_attempt_at' => 'datetime',
        'fix_attempts' => 'array',
        'ai_confidence' => 'decimal:2',
    ];

    /**
     * Get the scan that owns this issue
     */
    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }

    /**
     * Get the last scan where this issue was seen
     */
    public function lastSeenScan(): BelongsTo
    {
        return $this->belongsTo(Scan::class, 'last_seen_scan_id');
    }

    /**
     * Check if issue is critical
     */
    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }

    /**
     * Check if issue is warning
     */
    public function isWarning(): bool
    {
        return $this->severity === 'warning';
    }

    /**
     * Check if issue is info
     */
    public function isInfo(): bool
    {
        return $this->severity === 'info';
    }

    /**
     * Check if issue has AI fix
     */
    public function hasAiFix(): bool
    {
        return !empty($this->ai_fix);
    }

    /**
     * Check if issue is fixed
     */
    public function isFixed(): bool
    {
        return $this->fixed;
    }

    /**
     * Mark issue as fixed
     */
    public function markAsFixed(string $method = 'manual'): void
    {
        $this->update([
            'fixed' => true,
            'fixed_at' => now(),
            'fix_method' => $method,
        ]);
    }
    
    /**
     * Mark issue as skipped
     */
    public function markAsSkipped(string $reason): void
    {
        $this->update([
            'skipped' => true,
            'skipped_at' => now(),
            'skip_reason' => $reason,
        ]);
    }
    
    /**
     * Record a fix attempt
     */
    public function recordFixAttempt(string $status, ?string $error = null, ?array $data = null): void
    {
        $attempts = $this->fix_attempts ?? [];
        
        $attempts[] = [
            'timestamp' => now()->toIso8601String(),
            'status' => $status, // 'success', 'failed', 'skipped'
            'error' => $error,
            'data' => $data,
        ];
        
        // Keep only last 10 attempts
        if (count($attempts) > 10) {
            $attempts = array_slice($attempts, -10);
        }
        
        $this->update([
            'fix_attempts' => $attempts,
            'fix_attempt_count' => count($attempts),
            'last_fix_attempt_at' => now(),
        ]);
    }
    
    /**
     * Check if issue is skipped
     */
    public function isSkipped(): bool
    {
        return $this->skipped;
    }
    
    /**
     * Get last fix attempt
     */
    public function getLastFixAttempt(): ?array
    {
        $attempts = $this->fix_attempts ?? [];
        return empty($attempts) ? null : end($attempts);
    }

    /**
     * Get file name from path
     */
    public function getFileNameAttribute(): string
    {
        return basename($this->file_path);
    }

    /**
     * Get relative file path (removing base path)
     */
    public function getRelativePathAttribute(): string
    {
        $basePath = base_path();
        return str_replace($basePath . '/', '', $this->file_path);
    }

    /**
     * Get severity color for UI
     */
    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'critical' => 'red',
            'warning' => 'yellow',
            'info' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Get category icon for UI
     */
    public function getCategoryIconAttribute(): string
    {
        return match($this->category) {
            'security' => 'shield-exclamation',
            'performance' => 'bolt',
            'quality' => 'code',
            'laravel' => 'cube',
            default => 'exclamation-circle',
        };
    }

    /**
     * Get code context with line numbers
     */
    public function getCodeContextAttribute(): array
    {
        $context = $this->context;
        
        if (!isset($context['code']) || !is_array($context['code'])) {
            return [];
        }

        $lines = $context['code'];
        $startLine = $this->line_number - count($lines) + array_search($this->line_number, array_keys($lines)) + 1;
        
        $result = [];
        foreach ($lines as $index => $line) {
            $result[] = [
                'number' => $startLine + $index,
                'content' => $line,
                'is_issue_line' => ($startLine + $index) === $this->line_number,
            ];
        }

        return $result;
    }

    /**
     * Scope for critical issues
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    /**
     * Scope for warning issues
     */
    public function scopeWarning($query)
    {
        return $query->where('severity', 'warning');
    }

    /**
     * Scope for info issues
     */
    public function scopeInfo($query)
    {
        return $query->where('severity', 'info');
    }

    /**
     * Scope for fixed issues
     */
    public function scopeFixed($query)
    {
        return $query->where('fixed', true);
    }

    /**
     * Scope for unfixed issues
     */
    public function scopeUnfixed($query)
    {
        return $query->where('fixed', false);
    }

    /**
     * Scope for issues by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for issues by file
     */
    public function scopeByFile($query, string $filePath)
    {
        return $query->where('file_path', $filePath);
    }

    /**
     * Scope for issues with AI fixes
     */
    public function scopeWithAiFix($query)
    {
        return $query->whereNotNull('ai_fix');
    }

    /**
     * Scope for ordering by severity (critical first)
     */
    public function scopeOrderBySeverity($query)
    {
        return $query->orderByRaw("
            CASE severity 
                WHEN 'critical' THEN 1 
                WHEN 'warning' THEN 2 
                WHEN 'info' THEN 3 
                ELSE 4 
            END
        ");
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Rafaelogic\CodeSnoutr\Database\Factories\IssueFactory::new();
    }
}
