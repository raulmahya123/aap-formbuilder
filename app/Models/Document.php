<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model {
    protected $fillable = [
        'template_id','doc_no','dept_code','doc_type','project_code','revision_no',
        'effective_date','title','controlled_status','class',
        'header_config','footer_config','signature_config','sections','meta',
        'owner_id','department_id',
        // QR & Barcode
        'qr_text','qr_image_path','barcode_text','barcode_image_path',
    ];

    protected $casts = [
        'effective_date'   => 'date',
        'header_config'    => 'array',
        'footer_config'    => 'array',
        'signature_config' => 'array',
        'sections'         => 'array',
        'meta'             => 'array',
    ];

    // ===== Relations =====
    public function template(){ return $this->belongsTo(DocumentTemplate::class); }
    public function signatures(){ return $this->hasMany(DocumentSignature::class)->orderBy('order'); }
    public function owner(){ return $this->belongsTo(User::class,'owner_id'); }
    public function department(){ return $this->belongsTo(Department::class); }

    // ===== Helpers (opsional) =====

    /**
     * Merge header/footer dari template kalau di dokumen kosong.
     * Dipakai saat render supaya fallback ke template.
     */
    public function mergedHeader(): array {
        $tpl = $this->template?->header_config ?? [];
        return array_replace_recursive($tpl ?? [], $this->header_config ?? []);
    }
    public function mergedFooter(): array {
        $tpl = $this->template?->footer_config ?? [];
        return array_replace_recursive($tpl ?? [], $this->footer_config ?? []);
    }

    /**
     * Placeholder sederhana untuk string (mis. qr_text/barcode_text) seperti:
     * {doc_no}, {rev}, {dept}, {type}, {title}, {date}
     */
    public function resolvePlaceholders(string $text = null): ?string {
        if (!$text) return $text;
        $map = [
            '{doc_no}' => $this->doc_no,
            '{rev}'    => (string) $this->revision_no,
            '{dept}'   => $this->dept_code,
            '{type}'   => $this->doc_type,
            '{title}'  => $this->title,
            '{date}'   => optional($this->effective_date)->format('Y-m-d'),
        ];
        return strtr($text, $map);
    }
}
