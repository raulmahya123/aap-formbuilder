<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class DashboardDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Bersihkan tabel (aman untuk FK)
        $this->wipeTables([
            'document_acls',
            'documents',
            'document_templates',
            'form_entries',
            'forms',
            'departments',
            // 'users',
        ]);

        // 2) Isi data
        DB::transaction(function () {
            $now = Carbon::now();
            $defaultUserId = 1; // pakai ID user admin/dummy

            // ====== Users demo (opsional) ======
            $users = [
                ['id'=>1,'name'=>'Alice','email'=>'alice@example.com'],
                ['id'=>2,'name'=>'Bob','email'=>'bob@example.com'],
                ['id'=>3,'name'=>'Carol','email'=>'carol@example.com'],
            ];
            foreach ($users as $u) {
                DB::table('users')->updateOrInsert(
                    ['id'=>$u['id']],
                    [
                        'name'=>$u['name'],
                        'email'=>$u['email'],
                        'password'=>bcrypt('password'),
                        'created_at'=>$now, 'updated_at'=>$now
                    ]
                );
            }

            // ====== 10 Departments (dengan optional color) ======
            $deptDefs = [
                ['name'=>'Operasional',           'slug'=>'operasional', 'color'=>'#e61caf'],
                ['name'=>'HRGA',                  'slug'=>'hrga',        'color'=>'#ff3b30'],
                ['name'=>'Finance',               'slug'=>'finance',     'color'=>'#34c759'],
                ['name'=>'IT',                    'slug'=>'it',          'color'=>'#0ea5e9'],
                ['name'=>'HSE',                   'slug'=>'hse',         'color'=>'#f59e0b'],
                ['name'=>'SCM/Procurement',       'slug'=>'scm',         'color'=>'#8b5cf6'],
                ['name'=>'Engineering',           'slug'=>'engineering', 'color'=>'#ef4444'],
                ['name'=>'Marketing',             'slug'=>'marketing',   'color'=>'#14b8a6'],
                ['name'=>'Sales',                 'slug'=>'sales',       'color'=>'#f97316'],
                ['name'=>'Admin',                 'slug'=>'admin',       'color'=>'#64748b'],
            ];
            $hasColor = Schema::hasColumn('departments','color');

            $deptIds   = [];
            $deptNames = [];
            foreach ($deptDefs as $i => $def) {
                $id  = $i + 1; // keep deterministic IDs
                $row = [
                    'id'         => $id,
                    'name'       => $def['name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if (Schema::hasColumn('departments','slug'))  $row['slug']  = $def['slug'].'-'.$id;
                if ($hasColor)                                 $row['color'] = $def['color'];
                $this->fillAudit('departments', $row, $defaultUserId);

                DB::table('departments')->insert($row);

                $deptIds[]   = $id;
                $deptNames[] = $def['name'];
            }

            // ====== Forms: tiap departemen bikin MIN 3 (SOP, IK, FORM) ======
            // Kolom yang bisa ada: slug, doc_type, type(builder/pdf), schema, pdf_path, is_active, status
            $forms   = [];
            $formSeq = 1;

            $docTypes = ['SOP','IK','FORM'];
            $titleBank = [
                'SOP'  => ['Prosedur Operasional', 'Panduan Proses', 'Instruksi Kinerja'],
                'IK'   => ['Instruksi Kerja Mesin', 'Instruksi Kerja Shift', 'Instruksi Kalibrasi'],
                'FORM' => ['Form Pengajuan', 'Form Pemeriksaan', 'Form Checklist'],
            ];

            foreach ($deptIds as $idx => $deptId) {
                $deptName = $deptNames[$idx];

                foreach ($docTypes as $dt) {
                    // pilih judul base dari bank + departemen
                    $base = $titleBank[$dt][array_rand($titleBank[$dt])];
                    $title = "{$base} — {$deptName}";

                    // type: aman default 'builder' (biar tak wajib pdf_path)
                    $type  = 'builder';

                    $row = [
                        'id'            => $formSeq,
                        'department_id' => $deptId,
                        'title'         => $title,
                        'doc_type'      => $dt,           // <— penting untuk filter
                        'type'          => $type,         // builder/pdf
                        'schema'        => $type === 'builder' ? json_encode(['fields'=>[]]) : null,
                        'pdf_path'      => null,          // kalau type=pdf & butuh file, isi path sesuai storage kamu
                        'is_active'     => 1,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];

                    if (Schema::hasColumn('forms','slug'))    $row['slug'] = Str::slug($title).'-'.$formSeq;
                    if (Schema::hasColumn('forms','status'))  $row['status'] = 'published';

                    $this->fillAudit('forms', $row, $defaultUserId);

                    $forms[] = $row;
                    $formSeq++;
                }

                // (Opsional) tambah 1 form ekstra random supaya variasi jumlah
                $extraDt = $docTypes[array_rand($docTypes)];
                $extraTitle = "Dokumen Tambahan — {$deptName}";
                $rowExtra = [
                    'id'            => $formSeq,
                    'department_id' => $deptId,
                    'title'         => $extraTitle,
                    'doc_type'      => $extraDt,
                    'type'          => 'builder',
                    'schema'        => json_encode(['fields'=>[]]),
                    'pdf_path'      => null,
                    'is_active'     => (random_int(0,1) ? 1 : 0),
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
                if (Schema::hasColumn('forms','slug'))    $rowExtra['slug'] = Str::slug($extraTitle).'-'.$formSeq;
                if (Schema::hasColumn('forms','status'))  $rowExtra['status'] = 'published';
                $this->fillAudit('forms', $rowExtra, $defaultUserId);

                $forms[] = $rowExtra;
                $formSeq++;
            }

            DB::table('forms')->insert($forms);

            // ====== Document Templates (6 pcs) ======
            $templates = [];
            for ($i=1; $i<=6; $i++) {
                $name = 'Template #'.$i;
                $row = [
                    'id'         => $i,
                    'name'       => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if (Schema::hasColumn('document_templates','slug')) {
                    $row['slug'] = Str::slug($name).'-'.$i;
                }
                $this->fillAudit('document_templates', $row, $defaultUserId);
                $templates[] = $row;
            }
            DB::table('document_templates')->insert($templates);

            // ====== Documents & ACLs (30 hari) ======
            $documents = [];
            $docAcls   = [];
            $docId     = 1;
            for ($days=29; $days>=0; $days--) {
                $d = Carbon::today()->subDays($days);
                foreach ($deptIds as $k => $deptId) {
                    $count = random_int(0, 5);
                    for ($i=0; $i<$count; $i++) {
                        $tplId = random_int(1, count($templates));
                        $title = "Doc {$tplId} / ".$d->toDateString()." / ".$deptNames[$k];
                        $createdAt = $d->copy()->addMinutes(random_int(0, 1439));

                        $row = [
                            'id'                   => $docId,
                            'department_id'        => $deptId,
                            'document_template_id' => $tplId,
                            'title'                => $title,
                            'created_at'           => $createdAt,
                            'updated_at'           => $createdAt,
                        ];
                        if (Schema::hasColumn('documents','slug')) {
                            $row['slug'] = Str::slug("doc-{$tplId}-".$d->toDateString()."-".$deptId).'-'.$docId;
                        }
                        $this->fillAudit('documents', $row, $defaultUserId);
                        $documents[] = $row;

                        $userId = random_int(1, 3);
                        $acl = [
                            'document_id'  => $docId,
                            'user_id'      => $userId,
                            'department_id'=> $deptId,
                            'permission'   => 'read',
                            'created_at'   => $createdAt,
                            'updated_at'   => $createdAt,
                        ];
                        if (Schema::hasColumn('document_acls','created_by')) $acl['created_by'] = $defaultUserId;
                        if (Schema::hasColumn('document_acls','updated_by')) $acl['updated_by'] = $defaultUserId;
                        $docAcls[] = $acl;

                        $docId++;
                    }
                }
            }
            if ($documents) DB::table('documents')->insert($documents);
            if ($docAcls)   DB::table('document_acls')->insert($docAcls);

            // ====== Form Entries (30 hari) ======
            $formEntries = [];
            $entryId     = 1;
            $allFormIds  = array_column($forms, 'id');
            for ($days=29; $days>=0; $days--) {
                $d = Carbon::today()->subDays($days);
                foreach ($allFormIds as $fid) {
                    $count = random_int(0, 12); // sedikit lebih ringan
                    for ($i=0; $i<$count; $i++) {
                        $userId  = random_int(1, 3);
                        $created = $d->copy()->addMinutes(random_int(0, 1439));
                        $row = [
                            'id'         => $entryId,
                            'form_id'    => $fid,
                            'user_id'    => $userId,
                            'created_at' => $created,
                            'updated_at' => $created,
                        ];
                        if (Schema::hasColumn('form_entries','created_by')) $row['created_by'] = $userId;
                        if (Schema::hasColumn('form_entries','updated_by')) $row['updated_by'] = $userId;

                        $formEntries[] = $row;
                        $entryId++;
                    }
                }
            }
            foreach (array_chunk($formEntries, 2000) as $chunk) {
                DB::table('form_entries')->insert($chunk);
            }
        });
    }

    /** Isi kolom audit jika ada (created_by, updated_by, owner_id) */
    private function fillAudit(string $table, array &$row, int $userId): void
    {
        if (Schema::hasColumn($table, 'created_by') && !array_key_exists('created_by', $row)) {
            $row['created_by'] = $userId;
        }
        if (Schema::hasColumn($table, 'updated_by') && !array_key_exists('updated_by', $row)) {
            $row['updated_by'] = $userId;
        }
        if (Schema::hasColumn($table, 'owner_id') && !array_key_exists('owner_id', $row)) {
            $row['owner_id'] = $userId;
        }
    }

    /** Bersihkan tabel aman lintas driver */
    private function wipeTables(array $tables): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            Schema::disableForeignKeyConstraints();
            foreach ($tables as $t) DB::table($t)->truncate();
            Schema::enableForeignKeyConstraints();
            return;
        }

        if ($driver === 'pgsql') {
            $list = implode(', ', array_map(fn($t) => '"' . $t . '"', $tables));
            DB::statement("TRUNCATE TABLE {$list} RESTART IDENTITY CASCADE");
            return;
        }

        foreach ($tables as $t) DB::table($t)->delete();
        if ($driver === 'sqlite') {
            foreach ($tables as $t) {
                try { DB::statement("DELETE FROM sqlite_sequence WHERE name = ?", [$t]); } catch (\Throwable $e) {}
            }
        }
    }
}
