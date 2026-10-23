<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SheDocument extends Model
{
    use SoftDeletes;

    protected $table = 'she_documents';

    protected $fillable = [
        'document_code', 'title', 'category', 'description',
        'department_id', 'site_id', 'uploaded_by', 'approved_by',
        'version', 'revision_number', 'file_path', 'file_name', 'file_type', 'file_size',
        'effective_date', 'review_date', 'approved_date',
        'status', 'rejection_reason', 'access_roles',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'review_date' => 'date',
        'approved_date' => 'date',
        'access_roles' => 'array',
        'revision_number' => 'integer',
        'file_size' => 'integer',
    ];

    public const CATEGORIES = ['Policy', 'SOP', 'IK', 'Form', 'Other'];
    public const STATUSES = ['Draft', 'Submitted', 'In Review', 'Approved', 'Published', 'Obsolete'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function revisions()
    {
        return $this->hasMany(SheDocumentRevision::class, 'document_id');
    }

    public function scopeForSites(Builder $query, ?array $siteIds): Builder
    {
        return $siteIds === null ? $query : $query->whereIn('site_id', $siteIds ?: [-1]);
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereNotIn('status', ['Obsolete'])
            ->whereNotNull('review_date')
            ->whereDate('review_date', '<=', now()->addDays($days)->toDateString())
            ->whereDate('review_date', '>=', now()->toDateString());
    }

    public function scopeOverdueReview(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['Obsolete'])
            ->whereNotNull('review_date')
            ->whereDate('review_date', '<', now()->toDateString());
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->review_date
            && $this->review_date->isBetween(now(), now()->addDays($days))
            && !in_array($this->status, ['Obsolete']);
    }

    public function formattedFileSize(): string
    {
        if (!$this->file_size) {
            return '—';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }
        return round($size, 1) . ' ' . $units[$unit];
    }
}
