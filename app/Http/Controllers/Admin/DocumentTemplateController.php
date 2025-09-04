<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentTemplateController extends Controller
{
    /** Layout default (A4 px) */
    private const DEFAULT_LAYOUT = [
        'page'    => ['width' => 794, 'height' => 1123],
        'margins' => ['top' => 30, 'right' => 25, 'bottom' => 25, 'left' => 25],
        'font'    => ['size' => 11, 'family' => 'Poppins, sans-serif'],
    ];

    /* =======================
     * CRUD
     * ======================= */

    public function index()
    {
        $templates = DocumentTemplate::latest()->paginate(20);
        return view('admin.document_templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.document_templates.create');
    }

    /** Upload image (AJAX) dari file picker */
    public function storeImage(Request $request)
    {
        $request->validate([
            'file' => ['required','image','max:4096'],
        ]);

        $path = $request->file('file')->store('templates/photos', 'public');
        $url  = Storage::disk('public')->url($path);

        return response()->json([
            'ok'   => true,
            'path' => $path,
            'url'  => $url,
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'              => ['required', 'string', 'max:150'],
            'photo_path'        => ['nullable', 'image', 'max:2048'],
            'blocks_config'     => ['nullable'],
            'layout_config'     => ['nullable'],
            'header_config'     => ['nullable'],
            'footer_config'     => ['nullable'],
            'signature_config'  => ['nullable'],
        ]);

        // Foto template (opsional)
        $photoPath = null;
        if ($r->hasFile('photo_path')) {
            $photoPath = $r->file('photo_path')->store('templates/photos', 'public');
        }

        // Ambil & normalisasi payload
        $blocks    = $this->normalizeJson($data['blocks_config']    ?? null, []);
        $layout    = $this->normalizeJson($data['layout_config']    ?? null, self::DEFAULT_LAYOUT);
        $header    = $this->normalizeJson($data['header_config']    ?? null, []);
        $footer    = $this->normalizeJson($data['footer_config']    ?? null, []);
        $signature = $this->normalizeJson($data['signature_config'] ?? null, []);

        // Persist dataURL → file publik
        $blocks    = $this->replaceDataUrlsInBlocks($blocks);
        $header    = $this->replaceDataUrlsInHeader($header);
        $signature = $this->replaceDataUrlsInSignature($signature);

        // Pastikan layout minimal lengkap
        $layout = array_replace_recursive(self::DEFAULT_LAYOUT, $layout);

        DocumentTemplate::create([
            'name'              => $data['name'],
            'photo_path'        => $photoPath,
            'blocks_config'     => $blocks,
            'layout_config'     => $layout,
            'header_config'     => $header,
            'footer_config'     => $footer,
            'signature_config'  => $signature,
        ]);

        return redirect()->route('admin.document_templates.index')->with('success', 'Template dibuat');
    }

    public function edit(DocumentTemplate $template)
    {
        return view('admin.document_templates.edit', compact('template'));
    }

    public function update(Request $r, DocumentTemplate $template)
    {
        $data = $r->validate([
            'name'              => ['required', 'string', 'max:150'],
            'photo_path'        => ['nullable', 'image', 'max:2048'],
            'blocks_config'     => ['nullable'],
            'layout_config'     => ['nullable'],
            'header_config'     => ['nullable'],
            'footer_config'     => ['nullable'],
            'signature_config'  => ['nullable'],
            'remove_photo'      => ['nullable', 'in:1'],
        ]);

        $payload = ['name' => $data['name']];

        // Hapus foto existing bila diminta
        if ($r->boolean('remove_photo') && $template->photo_path) {
            Storage::disk('public')->delete($template->photo_path);
            $payload['photo_path'] = null;
        }

        // Ganti foto jika ada upload baru
        if ($r->hasFile('photo_path')) {
            if ($template->photo_path) {
                Storage::disk('public')->delete($template->photo_path);
            }
            $payload['photo_path'] = $r->file('photo_path')->store('templates/photos', 'public');
        }

        // Konfigurasi opsional
        if ($r->has('blocks_config')) {
            $blocks = $this->normalizeJson($data['blocks_config'] ?? null, []);
            $payload['blocks_config'] = $this->replaceDataUrlsInBlocks($blocks);
        }
        if ($r->has('layout_config')) {
            $layout = $this->normalizeJson($data['layout_config'] ?? null, self::DEFAULT_LAYOUT);
            $payload['layout_config'] = array_replace_recursive(self::DEFAULT_LAYOUT, $layout);
        }
        if ($r->has('header_config')) {
            $header = $this->normalizeJson($data['header_config'] ?? null, []);
            $payload['header_config'] = $this->replaceDataUrlsInHeader($header);
        }
        if ($r->has('footer_config')) {
            $footer = $this->normalizeJson($data['footer_config'] ?? null, []);
            $payload['footer_config'] = $footer;
        }
        if ($r->has('signature_config')) {
            $signature = $this->normalizeJson($data['signature_config'] ?? null, []);
            $payload['signature_config'] = $this->replaceDataUrlsInSignature($signature);
        }

        $template->update($payload);

        return redirect()->route('admin.document_templates.index')->with('success', 'Template diperbarui');
    }

    public function destroy(DocumentTemplate $template)
    {
        if ($template->photo_path) {
            Storage::disk('public')->delete($template->photo_path);
        }
        $template->delete();
        return redirect()->route('admin.document_templates.index')->with('success', 'Template dihapus');
    }

    public function show(DocumentTemplate $template)
    {
        [$layout, $blocks] = $this->buildPreviewData($template);

        return view('admin.document_templates.show', [
            'template' => $template,
            'layout'   => $layout,
            'blocks'   => $blocks,
            'name'     => $template->name,
        ]);
    }

    /* =======================
     * Helpers
     * ======================= */

    /** Terima array (hasil casts) atau string JSON → array aman */
    private function normalizeJson($value, $default = []): array
    {
        if (is_array($value)) return $value;
        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') return $default;
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
        }
        return $default;
    }

    /** Persist dataURL image ke storage publik. Return URL /storage/...; null jika bukan dataURL */
    private function persistDataUrlImage(string $dataUrl, string $dir = 'templates/blocks'): ?string
    {
        if (!preg_match('#^data:image/([a-zA-Z0-9\+\-\.]+);base64,#', $dataUrl, $m)) {
            return null;
        }
        $ext = strtolower($m[1]);
        $ext = str_replace(['jpeg','svg+xml'], ['jpg','svg'], $ext);

        $base64 = preg_replace('#^data:image/[a-zA-Z0-9\+\-\.]+;base64,#', '', $dataUrl);
        $bytes  = base64_decode($base64, true);
        if ($bytes === false) return null;

        $filename = Str::random(40) . '.' . $ext;
        $path = trim($dir, '/').'/'.$filename;

        Storage::disk('public')->put($path, $bytes);

        return Storage::url($path);
    }

    /** Bersihkan & persist gambar di blocks (image.src & signature.src) */
    private function replaceDataUrlsInBlocks(array $blocks): array
    {
        foreach ($blocks as &$b) {
            $type = $b['type'] ?? null;

            if ($type === 'image' && !empty($b['src']) && is_string($b['src'])) {
                if (str_starts_with($b['src'], 'data:image/')) {
                    if ($url = $this->persistDataUrlImage($b['src'], 'templates/blocks')) {
                        $b['src'] = $url;
                    }
                } elseif (str_starts_with($b['src'], 'blob:')) {
                    $b['src'] = '';
                }
            }

            if ($type === 'signature' && !empty($b['src']) && is_string($b['src'])) {
                if (str_starts_with($b['src'], 'data:image/')) {
                    if ($url = $this->persistDataUrlImage($b['src'], 'templates/signatures')) {
                        $b['src'] = $url;
                    }
                } elseif (str_starts_with($b['src'], 'blob:')) {
                    $b['src'] = '';
                }
            }
        }
        unset($b);
        return $blocks;
    }

    /** Bersihkan & persist gambar di header.items (type=image → src) */
    private function replaceDataUrlsInHeader(array $header): array
    {
        $items = $header['items'] ?? null;
        if (is_array($items) && $items) {
            foreach ($items as &$it) {
                if (($it['type'] ?? null) === 'image' && !empty($it['src']) && is_string($it['src'])) {
                    if (str_starts_with($it['src'], 'data:image/')) {
                        if ($url = $this->persistDataUrlImage($it['src'], 'templates/blocks')) {
                            $it['src'] = $url;
                        }
                    } elseif (str_starts_with($it['src'], 'blob:')) {
                        $it['src'] = '';
                    }
                }
            }
            unset($it);
        }
        return $header;
    }

    /** Bersihkan & persist gambar pada signature.rows (image_path) */
    private function replaceDataUrlsInSignature(array $signature): array
    {
        $rows = $signature['rows'] ?? null;
        if (is_array($rows) && $rows) {
            foreach ($rows as &$row) {
                if (!empty($row['image_path']) && is_string($row['image_path'])) {
                    if (str_starts_with($row['image_path'], 'data:image/')) {
                        if ($url = $this->persistDataUrlImage($row['image_path'], 'templates/signatures')) {
                            $row['image_path'] = $url;
                        }
                    } elseif (str_starts_with($row['image_path'], 'blob:')) {
                        $row['image_path'] = '';
                    }
                }
            }
            unset($row);
        }
        return $signature;
    }

    /** Ambil layout & blocks siap preview (aman untuk casts/JSON) */
    private function buildPreviewData(DocumentTemplate $template): array
    {
        $layout = $this->toArray($template->layout_config, self::DEFAULT_LAYOUT);
        $layout = array_replace_recursive(self::DEFAULT_LAYOUT, $layout);

        $blocks = $this->toArray($template->blocks_config, []);

        $defFontFamily = $layout['font']['family'] ?? 'Poppins, sans-serif';
        $defFontSizePt = $layout['font']['size']   ?? 11;

        foreach ($blocks as &$b) {
            $b['id']          = $b['id']          ?? uniqid();
            $b['top']         = isset($b['top'])   ? (int)$b['top']   : 0;
            $b['left']        = isset($b['left'])  ? (int)$b['left']  : 0;
            $b['width']       = isset($b['width']) ? (int)$b['width'] : 80;
            $b['height']      = isset($b['height'])? (int)$b['height']: 24;
            $b['z']           = $b['z']           ?? 1;
            $b['border']      = (bool)($b['border'] ?? false);
            $b['borderColor'] = $b['borderColor']  ?? '#e5e7eb';
            $b['type']        = $b['type']         ?? 'text';

            switch ($b['type']) {
                case 'text':
                    $b['text']       = $b['text']       ?? '';
                    $b['align']      = $b['align']      ?? 'left';
                    $b['bold']       = (bool)($b['bold'] ?? false);
                    $b['fontSize']   = isset($b['fontSize']) ? (int)$b['fontSize'] : $defFontSizePt;
                    $b['fontFamily'] = $b['fontFamily'] ?? $defFontFamily;
                    $b['color']      = $b['color']      ?? '#111';
                    break;

                case 'image':
                    $b['src'] = $b['src'] ?? '';
                    break;

                case 'tableCell':
                    $b['text']     = $b['text'] ?? '—';
                    $b['bold']     = (bool)($b['bold'] ?? false);
                    $b['fontSize'] = isset($b['fontSize']) ? (int)$b['fontSize'] : 12;
                    break;

                case 'footer':
                    $b['text']     = $b['text'] ?? '© Perusahaan 2025';
                    $b['align']    = $b['align'] ?? 'left';
                    $b['fontSize'] = isset($b['fontSize']) ? (int)$b['fontSize'] : 11;
                    $b['color']    = $b['color'] ?? '#111';
                    $b['showPage'] = (bool)($b['showPage'] ?? false);
                    break;

                case 'signature':
                    $b['role']              = $b['role']              ?? 'Role';
                    $b['name']              = $b['name']              ?? 'Nama';
                    $b['position']          = $b['position']          ?? 'Jabatan';
                    $b['signatureText']     = $b['signatureText']     ?? '';
                    $b['src']               = $b['src']               ?? '';
                    $b['align']             = $b['align']             ?? 'center';
                    $b['fontFamily']        = $b['fontFamily']        ?? $defFontFamily;
                    $b['infoFontSize']      = isset($b['infoFontSize']) ? (int)$b['infoFontSize'] : 11;
                    $b['signatureFontSize'] = isset($b['signatureFontSize']) ? (int)$b['signatureFontSize'] : 16;
                    break;
            }
        }
        unset($b);

        return [$layout, $blocks];
    }

    /** Terima array atau string JSON → array */
    private function toArray(mixed $val, array $fallback = []): array
    {
        if (is_array($val)) return $val;
        if (is_string($val) && $val !== '') {
            $decoded = json_decode($val, true);
            if (is_array($decoded)) return $decoded;
        }
        return $fallback;
    }

    /**
     * (Opsional) Merakit blocks dari header/footer/signature untuk preview alternatif.
     * Tidak dipakai di show() saat ini, tapi disediakan bila diperlukan.
     */
    private function buildBlocksFromConfigs(array $layout, array $header, array $footer, array $signature): array
    {
        $blocks = [];
        $makeId = static fn() => substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 8);

        $hItems = data_get($header, 'items', []);
        if (is_array($hItems) && $hItems) {
            foreach ($hItems as $it) {
                $type = $it['type'] ?? 'text';
                $blocks[] = [
                    'id'   => $makeId(), 'type' => $type, 'z' => 10,
                    'top'  => (int)($it['top'] ?? 0),   'left' => (int)($it['left'] ?? 0),
                    'width'=> (int)($it['width'] ?? 160),'height'=> (int)($it['height'] ?? 32),
                    'text' => $it['text'] ?? '',        'src'  => $it['src']  ?? '',
                    'bold' => (bool)($it['bold'] ?? false),
                    'fontSize' => (int)($it['font_size'] ?? ($layout['font']['size'] ?? 11)),
                    'align'    => $it['align'] ?? 'left',
                ];
            }
        } else {
            if ($logo = data_get($header, 'logo.url')) {
                $blocks[] = ['id'=>$makeId(),'type'=>'image','src'=>$logo,'top'=>20,'left'=>20,'width'=>120,'height'=>48,'z'=>10];
            }
            if ($title = data_get($header, 'title.text')) {
                $blocks[] = [
                    'id'=>$makeId(),'type'=>'text','text'=>$title,'bold'=>true,
                    'align'=> data_get($header,'title.align','left'),
                    'fontSize'=>18,
                    'top'=>($layout['margins']['top'] ?? 30) + 8,
                    'left'=>($layout['margins']['left'] ?? 25) + 160,
                    'width'=>400,'height'=>40,'z'=>10,
                ];
            }
        }

        $fItems = data_get($footer, 'items', []);
        if (is_array($fItems) && $fItems) {
            foreach ($fItems as $f) {
                $blocks[] = [
                    'id'=>$makeId(),'type'=>'footer','z'=>5,
                    'text'=> $f['text'] ?? '',
                    'showPage'=> !empty($f['show_page_number']),
                    'align'=> $f['align'] ?? 'left',
                    'fontSize'=> (int)($f['font_size'] ?? 11),
                    'top'=> (int)($f['top'] ?? ($layout['page']['height'] - ($layout['margins']['bottom'] ?? 25) - 36)),
                    'left'=> (int)($f['left'] ?? ($layout['margins']['left'] ?? 25)),
                    'width'=> (int)($f['width'] ?? ($layout['page']['width'] - ($layout['margins']['left'] ?? 25) - ($layout['margins']['right'] ?? 25))),
                    'height'=> (int)($f['height'] ?? 36),
                ];
            }
        } elseif (!empty($footer['text'])) {
            $blocks[] = [
                'id'=>$makeId(),'type'=>'footer','z'=>5,
                'text'=> $footer['text'] ?? '',
                'showPage'=> !empty($footer['show_page_number']),
                'align'=>'left','fontSize'=>11,
                'top'=> $layout['page']['height'] - ($layout['margins']['bottom'] ?? 25) - 36,
                'left'=> $layout['margins']['left'] ?? 25,
                'width'=> $layout['page']['width'] - ($layout['margins']['left'] ?? 25) - ($layout['margins']['right'] ?? 25),
                'height'=>36,
            ];
        }

        $sRows = data_get($signature, 'rows', []);
        if (is_array($sRows) && $sRows) {
            foreach ($sRows as $row) {
                $blocks[] = [
                    'id'=>$makeId(),'type'=>'signature','z'=>10,
                    'role'=>$row['role'] ?? '',
                    'name'=>$row['name'] ?? '',
                    'position'=>$row['position_title'] ?? '',
                    'signatureText'=>$row['signature_text'] ?? '',
                    'src'=>$row['image_path'] ?? '',
                    'top'=> (int)($row['top'] ?? 0),
                    'left'=> (int)($row['left'] ?? 0),
                    'width'=> (int)($row['width'] ?? 160),
                    'height'=> (int)($row['height'] ?? 70),
                ];
            }
        }

        return $blocks;
    }
}
