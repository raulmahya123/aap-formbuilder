<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // ★ dipakai untuk nama file random

class DocumentTemplateController extends Controller
{
    // ★ Tambah font.family default
    private const DEFAULT_LAYOUT = [
        'page'    => ['width' => 794, 'height' => 1123],
        'margins' => ['top' => 30, 'right' => 25, 'bottom' => 25, 'left' => 25],
        'font'    => ['size' => 11, 'family' => 'Poppins, sans-serif'],
    ];

    public function index()
    {
        $templates = DocumentTemplate::latest()->paginate(20);
        return view('admin.document_templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.document_templates.create');
    }

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

        // Upload foto template (opsional)
        $photoPath = null;
        if ($r->hasFile('photo_path')) {
            $photoPath = $r->file('photo_path')->store('templates/photos', 'public');
        }

        // Ambil & normalisasi JSON dari request
        $blocks    = $this->normalizeJson($data['blocks_config']    ?? null, []);
        $layout    = $this->normalizeJson($data['layout_config']    ?? null, self::DEFAULT_LAYOUT);
        $header    = $this->normalizeJson($data['header_config']    ?? null, []);
        $footer    = $this->normalizeJson($data['footer_config']    ?? null, []);
        $signature = $this->normalizeJson($data['signature_config'] ?? null, []);

        // ★ Persist semua dataURL gambar ke storage publik
        $blocks    = $this->replaceDataUrlsInBlocks($blocks);
        $header    = $this->replaceDataUrlsInHeader($header);
        $signature = $this->replaceDataUrlsInSignature($signature);

        $payload = [
            'name'              => $data['name'],
            'photo_path'        => $photoPath,
            'blocks_config'     => $blocks,
            'layout_config'     => $layout,
            'header_config'     => $header,
            'footer_config'     => $footer,
            'signature_config'  => $signature,
        ];

        DocumentTemplate::create($payload);

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
            'remove_photo'      => ['nullable', 'in:1'], // ★ dukung hapus foto
        ]);

        $payload = ['name' => $data['name']];

        // ★ Hapus foto existing jika diminta
        if ($r->boolean('remove_photo') && $template->photo_path) {
            Storage::disk('public')->delete($template->photo_path);
            $payload['photo_path'] = null;
        }

        // Ganti foto jika upload baru
        if ($r->hasFile('photo_path')) {
            if ($template->photo_path) {
                Storage::disk('public')->delete($template->photo_path);
            }
            $payload['photo_path'] = $r->file('photo_path')->store('templates/photos', 'public');
        }

        // Update konfigurasi (opsional jika dikirim)
        if ($r->has('blocks_config')) {
            $blocks = $this->normalizeJson($data['blocks_config'] ?? null, []);
            $payload['blocks_config'] = $this->replaceDataUrlsInBlocks($blocks); // ★
        }
        if ($r->has('layout_config')) {
            $layout = $this->normalizeJson($data['layout_config'] ?? null, self::DEFAULT_LAYOUT);
            // ★ pastikan field wajib ada (merge ringan)
            $layout = array_replace_recursive(self::DEFAULT_LAYOUT, $layout);
            $payload['layout_config'] = $layout;
        }
        if ($r->has('header_config')) {
            $header = $this->normalizeJson($data['header_config'] ?? null, []);
            $payload['header_config'] = $this->replaceDataUrlsInHeader($header); // ★
        }
        if ($r->has('footer_config')) {
            $footer = $this->normalizeJson($data['footer_config'] ?? null, []);
            $payload['footer_config'] = $footer;
        }
        if ($r->has('signature_config')) {
            $signature = $this->normalizeJson($data['signature_config'] ?? null, []);
            $payload['signature_config'] = $this->replaceDataUrlsInSignature($signature); // ★
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

    private function normalizeJson($value, $default = [])
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

    // ★ Simpan dataURL ke storage & return URL publik; null jika bukan dataURL image
    private function persistDataUrlImage(string $dataUrl, string $dir = 'templates/blocks'): ?string
    {
        if (!preg_match('#^data:image/([a-zA-Z0-9\+\-\.]+);base64,#', $dataUrl, $m)) {
            return null;
        }
        $ext = strtolower($m[1]);

        // Normalize beberapa ekstensi umum
        $ext = str_replace('jpeg', 'jpg', $ext);
        $ext = str_replace('svg+xml', 'svg', $ext);

        $base64 = preg_replace('#^data:image/[a-zA-Z0-9\+\-\.]+;base64,#', '', $dataUrl);
        $bytes  = base64_decode($base64, true);
        if ($bytes === false) return null;

        $filename = Str::random(40) . '.' . $ext;
        $path = $dir . '/' . $filename;

        Storage::disk('public')->put($path, $bytes);

        return Storage::url($path); // "/storage/dir/file.ext"
    }

    // ★ Bersihkan & persist gambar di blocks (type=image & signature image)
    private function replaceDataUrlsInBlocks(array $blocks): array
    {
        foreach ($blocks as &$b) {
            if (($b['type'] ?? null) === 'image' && !empty($b['src']) && is_string($b['src'])) {
                if (str_starts_with($b['src'], 'data:image/')) {
                    if ($url = $this->persistDataUrlImage($b['src'], 'templates/blocks')) {
                        $b['src'] = $url;
                    }
                } elseif (str_starts_with($b['src'], 'blob:')) {
                    // blob tidak valid untuk server; kosongkan
                    $b['src'] = '';
                }
            }
            // signature block mungkin simpan di "src" juga
            if (($b['type'] ?? null) === 'signature' && !empty($b['src']) && is_string($b['src'])) {
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

    // ★ Bersihkan & persist gambar di header.items (type=image → src)
    private function replaceDataUrlsInHeader(array $header): array
    {
        if (!empty($header['items']) && is_array($header['items'])) {
            foreach ($header['items'] as &$it) {
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

    // ★ Bersihkan & persist gambar pada signature.rows (image_path)
    private function replaceDataUrlsInSignature(array $signature): array
    {
        if (!empty($signature['rows']) && is_array($signature['rows'])) {
            foreach ($signature['rows'] as &$row) {
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

    private function buildPreviewData(DocumentTemplate $template): array
    {
        $layout    = $this->toArray($template->layout_config);
        $blocks    = $this->toArray($template->blocks_config);
        $header    = $this->toArray($template->header_config);
        $footer    = $this->toArray($template->footer_config);
        $signature = $this->toArray($template->signature_config);

        $layout = array_replace_recursive(self::DEFAULT_LAYOUT, $layout ?? []);

        if (empty($blocks)) {
            $blocks = $this->buildBlocksFromConfigs($layout, $header, $footer, $signature);
        }

        if (empty($blocks)) {
            $blocks = [[
                'id'       => uniqid('blk_'),
                'type'     => 'text',
                'text'     => 'Contoh isi dokumen',
                'top'      => 120,
                'left'     => 100,
                'width'    => 360,
                'height'   => 44,
                'fontSize' => 16,
                'bold'     => true,
                'align'    => 'left',
                'z'        => 10,
            ]];
        }

        return [$layout, $blocks];
    }

    private function toArray($value): array
    {
        if (is_array($value)) return $value;
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    }

    private function buildBlocksFromConfigs(array $layout, array $header, array $footer, array $signature): array
    {
        $blocks = [];
        $makeId = static fn() => substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 8);

        $hItems = data_get($header, 'items', []);
        if (is_array($hItems) && $hItems) {
            foreach ($hItems as $it) {
                $type = $it['type'] ?? 'text';
                $blocks[] = [
                    'id'   => $makeId(), 'type' => $type,
                    'top'  => (int)($it['top'] ?? 0),   'left' => (int)($it['left'] ?? 0),
                    'width'=> (int)($it['width'] ?? 160),'height'=> (int)($it['height'] ?? 32),
                    'text' => $it['text'] ?? '',        'src'  => $it['src']  ?? '',
                    'bold' => (bool)($it['bold'] ?? false),
                    'fontSize' => (int)($it['font_size'] ?? ($layout['font']['size'] ?? 11)),
                    'align'    => $it['align'] ?? 'left',
                    'z' => 10,
                ];
            }
        } else {
            if ($logo = data_get($header, 'logo.url')) {
                $blocks[] = ['id'=>$makeId(),'type'=>'image','src'=>$logo,'top'=>20,'left'=>20,'width'=>120,'height'=>48,'z'=>10];
            }
            if ($title = data_get($header, 'title.text')) {
                $blocks[] = [
                    'id'=>$makeId(),'type'=>'text','text'=>$title,
                    'align'=> data_get($header,'title.align','left'),
                    'bold'=>true,'fontSize'=>18,
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
                    'id'=>$makeId(),'type'=>'footer',
                    'text'=> $f['text'] ?? '',
                    'showPage'=> !empty($f['show_page_number']),
                    'align'=> $f['align'] ?? 'left',
                    'fontSize'=> (int)($f['font_size'] ?? 11),
                    'top'=> (int)($f['top'] ?? ($layout['page']['height'] - ($layout['margins']['bottom'] ?? 25) - 36)),
                    'left'=> (int)($f['left'] ?? ($layout['margins']['left'] ?? 25)),
                    'width'=> (int)($f['width'] ?? ($layout['page']['width'] - ($layout['margins']['left'] ?? 25) - ($layout['margins']['right'] ?? 25))),
                    'height'=> (int)($f['height'] ?? 36),
                    'z'=>5,
                ];
            }
        } elseif (!empty($footer['text'])) {
            $blocks[] = [
                'id'=>$makeId(),'type'=>'footer',
                'text'=> $footer['text'] ?? '',
                'showPage'=> !empty($footer['show_page_number']),
                'align'=>'left','fontSize'=>11,
                'top'=> $layout['page']['height'] - ($layout['margins']['bottom'] ?? 25) - 36,
                'left'=> $layout['margins']['left'] ?? 25,
                'width'=> $layout['page']['width'] - ($layout['margins']['left'] ?? 25) - ($layout['margins']['right'] ?? 25),
                'height'=>36,'z'=>5,
            ];
        }

        $sRows = data_get($signature, 'rows', []);
        if (is_array($sRows) && $sRows) {
            foreach ($sRows as $row) {
                $blocks[] = [
                    'id'=>$makeId(),'type'=>'signature',
                    'role'=>$row['role'] ?? '',
                    'name'=>$row['name'] ?? '',
                    'position'=>$row['position_title'] ?? '',
                    'signatureText'=>$row['signature_text'] ?? '',
                    'src'=>$row['image_path'] ?? '',
                    'top'=> (int)($row['top'] ?? 0),
                    'left'=> (int)($row['left'] ?? 0),
                    'width'=> (int)($row['width'] ?? 160),
                    'height'=> (int)($row['height'] ?? 70),
                    'z'=>10,
                ];
            }
        }

        return $blocks;
    }
}
