<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/DocumentTemplate.php
class DocumentTemplate extends Model
{
    protected $fillable = [
        'name',
        'photo_path',        // ← tambahin biar bisa mass-assignment
        'blocks_config',
        'layout_config',
        'header_config',
        'footer_config',
        'signature_config',
    ];

    protected $casts = [
        'blocks_config'    => 'array',
        'layout_config'    => 'array',
        'header_config'    => 'array',
        'footer_config'    => 'array',
        'signature_config' => 'array',
    ];

    /**
     * Helper akses URL foto (otomatis ke storage/public).
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) return null;

        if (\Illuminate\Support\Facades\Storage::disk('mandala_uploads')->exists($this->photo_path)) {
            return \Illuminate\Support\Facades\Route::has('pubfile.stream')
                ? route('pubfile.stream', ['path' => $this->photo_path])
                : asset('storage/' . $this->photo_path);
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->photo_path)) {
            return asset('storage/' . $this->photo_path);
        }

        return null;
    }
}
