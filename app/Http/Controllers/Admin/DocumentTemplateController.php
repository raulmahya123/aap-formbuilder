<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentTemplateController extends Controller
{
    // Layout standar A4 (px) + margin & font
    private const DEFAULT_LAYOUT = [
        'page'    => ['width' => 794, 'height' => 1123],
        'margins' => ['top' => 30, 'right' => 25, 'bottom' => 25, 'left' => 25],
        'font'    => ['size' => 11],
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

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'              => ['required', 'string', 'max:150'],
            'photo_path'        => ['nullable', 'image', 'max:2048'], // ← foto template
            'blocks_config'     => ['nullable'],
            'layout_config'     => ['nullable'],
            'header_config'     => ['nullable'],
            'footer_config'     => ['nullable'],
            'signature_config'  => ['nullable'],
        ]);

        // Upload foto jika ada
        if ($r->hasFile('photo_path')) {
            $path = $r->file('photo_path')->store('templates/photos', 'public');
            $data['photo_path'] = $path; // simpan PATH (bukan URL)
        }

        $payload = [
            'name'              => $data['name'],
            'photo_path'        => $data['photo_path'] ?? null,
            'blocks_config'     => $this->normalizeJson($data['blocks_config']  ?? null, []),
            // Pakai DEFAULT_LAYOUT sebagai default
            'layout_config'     => $this->normalizeJson($data['layout_config']  ?? null, self::DEFAULT_LAYOUT),
            'header_config'     => $this->normalizeJson($data['header_config']  ?? null, []),
            'footer_config'     => $this->normalizeJson($data['footer_config']  ?? null, []),
            'signature_config'  => $this->normalizeJson($data['signature_config'] ?? null, []),
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
            'photo_path'        => ['nullable', 'image', 'max:2048'], // ← foto template
            'blocks_config'     => ['nullable'],
            'layout_config'     => ['nullable'],
            'header_config'     => ['nullable'],
            'footer_config'     => ['nullable'],
            'signature_config'  => ['nullable'],
        ]);

        $payload = ['name' => $data['name']];

        // Replace foto jika upload baru
        if ($r->hasFile('photo_path')) {
            if ($template->photo_path) {
                Storage::disk('public')->delete($template->photo_path);
            }
            $path = $r->file('photo_path')->store('templates/photos', 'public');
            $payload['photo_path'] = $path; // simpan PATH (bukan URL)
        }

        // Update konfigurasi (opsional jika dikirim)
        foreach (['blocks_config', 'layout_config', 'header_config', 'footer_config', 'signature_config'] as $k) {
            if ($r->has($k)) {
                $default = $k === 'layout_config' ? self::DEFAULT_LAYOUT : [];
                $payload[$k] = $this->normalizeJson($data[$k] ?? null, $default);
            }
        }

        $template->update($payload);

        return redirect()->route('admin.document_templates.index')->with('success', 'Template diperbarui');
    }

    public function destroy(DocumentTemplate $template)
    {
        // Hapus file foto jika ada
        if ($template->photo_path) {
            Storage::disk('public')->delete($template->photo_path);
        }

        $template->delete();
        return redirect()->route('admin.document_templates.index')->with('success', 'Template dihapus');
    }

    /**
     * Tampilkan preview template.
     * Controller merakit layout & blocks agar Blade tinggal render.
     */
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

    /**
     * Rakitan data untuk preview:
     * - Normalisasi layout (merge DEFAULT_LAYOUT)
     * - Build $blocks dari header/footer/signature jika blocks kosong
     * - Fallback dummy block bila tetap kosong
     */
    private function buildPreviewData(DocumentTemplate $template): array
    {
        $layout    = $this->toArray($template->layout_config);
        $blocks    = $this->toArray($template->blocks_config);
        $header    = $this->toArray($template->header_config);
        $footer    = $this->toArray($template->footer_config);
        $signature = $this->toArray($template->signature_config);

        // Merge layout dengan default
        $layout = array_replace_recursive(self::DEFAULT_LAYOUT, $layout ?? []);

        // Build blocks dari config jika kosong
        if (empty($blocks)) {
            $blocks = $this->buildBlocksFromConfigs($layout, $header, $footer, $signature);
        }

        // Fallback: tetap kosong → suntik 1 block dummy
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

    /** Ubah value jadi array (terima array atau JSON string). */
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

    /**
     * Builder blocks dari header/footer/signature (skema baru & legacy).
     */
    private function buildBlocksFromConfigs(array $layout, array $header, array $footer, array $signature): array
    {
        $blocks = [];
        $makeId = static fn() => substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 8);

        /* ===== HEADER ===== */
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
            // Legacy: logo + title
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

        /* ===== FOOTER ===== */
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
        } elseif (!empty($footer['text'])) { // Legacy single item
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

        /* ===== SIGNATURE ===== */
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
