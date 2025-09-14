<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    // Optional: konstanta buat konsistensi nilai
    public const CONTROLLED   = 'controlled';
    public const UNCONTROLLED = 'uncontrolled';
    public const OBSOLETE     = 'obsolete';

    public const CLASS_I   = 'I';
    public const CLASS_II  = 'II';
    public const CLASS_III = 'III';
    public const CLASS_IV  = 'IV';

    protected $fillable = [
        'template_id',
        'title', 'dept_code', 'doc_type', 'project_code', 'revision_no',
        'effective_date', 'controlled_status', 'class', 'department_id',
        'doc_no', 'owner_id',
        'layout_config', 'header_config', 'footer_config', 'signature_config', 'sections',
        'qr_text', 'barcode_text', // kalau dipakai di controller
    ];

    protected $casts = [
        'effective_date'   => 'date',
        'layout_config'    => 'array',
        'header_config'    => 'array',
        'footer_config'    => 'array',
        'signature_config' => 'array',
        'sections'         => 'array',
    ];

    // Optional: default attribute
    protected $attributes = [
        'controlled_status' => self::CONTROLLED,
    ];

    // Relasi
    public function template(): BelongsTo { return $this->belongsTo(DocumentTemplate::class, 'template_id'); }
    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'owner_id'); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function signatures(): HasMany { return $this->hasMany(DocumentSignature::class); }
    public function acls(): HasMany { return $this->hasMany(DocumentAcl::class); }

    // Helper
    public function bumpRevision(): void
    {
        $this->revision_no = (int)($this->revision_no ?? 0) + 1;
    }

    // Optional: accessor “aman” yang selalu kasih array (bukan null)
    public function getLayoutConfigAttribute($value): array
    {
        $arr = $value ?? [];
        if (!is_array($arr)) return [];
        // merge minimal default (biar view tetap aman)
        return array_replace_recursive([
            'page'    => ['width' => 794, 'height' => 1123],
            'margins' => ['top' => 30, 'right' => 25, 'bottom' => 25, 'left' => 25],
            'font'    => ['size' => 11, 'family' => 'Poppins, sans-serif'],
        ], $arr);
    }

    // Optional: kecilin resiko input aneh (trim & uppercase) untuk kode2
    public function setDeptCodeAttribute($v): void
    {
        $this->attributes['dept_code'] = $v ? strtoupper(trim($v)) : null;
    }
    public function setDocTypeAttribute($v): void
    {
        $this->attributes['doc_type'] = $v ? strtoupper(trim($v)) : null;
    }
    public function setProjectCodeAttribute($v): void
    {
        $this->attributes['project_code'] = $v ? strtoupper(trim($v)) : null;
    }
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->withTrashed()
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->firstOrFail();
    }
}
