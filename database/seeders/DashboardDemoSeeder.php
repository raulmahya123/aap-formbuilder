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

            // ====== Departments ======
            $deptNames = ['Operations','Finance','HR','IT','Legal'];
            $deptIds = [];
            foreach ($deptNames as $i => $nm) {
                $id = $i + 1;
                $row = [
                    'id'         => $id,
                    'name'       => $nm,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if (Schema::hasColumn('departments','slug')) {
                    $row['slug'] = Str::slug($nm).'-'.$id;
                }
                $this->fillAudit('departments', $row, $defaultUserId); // <<< NEW
                DB::table('departments')->insert($row);
                $deptIds[] = $id;
            }

            // ====== Forms (tiap dept 2 form) ======
            $formId = 1;
            $forms = [];
            foreach ($deptIds as $deptId) {
                for ($k=1;$k<=2;$k++) {
                    $title = match ($deptId) {
                        1 => ($k===1 ? 'Daily Ops Report' : 'Equipment Checklist'),
                        2 => ($k===1 ? 'Expense Claim' : 'Budget Request'),
                        3 => ($k===1 ? 'Leave Request' : 'Recruitment Request'),
                        4 => ($k===1 ? 'IT Support Ticket' : 'Access Request'),
                        5 => ($k===1 ? 'Contract Review' : 'Compliance Report'),
                        default => "Form $deptId-$k",
                    };
                    $row = [
                        'id'            => $formId,
                        'department_id' => $deptId,
                        'title'         => $title,
                        'is_active'     => ($k % 2 === 1) ? 1 : 0,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                    if (Schema::hasColumn('forms','slug')) {
                        $row['slug'] = Str::slug($title).'-'.$formId;
                    }
                    // jika ada kolom status NOT NULL tanpa default, boleh set di sini:
                    if (Schema::hasColumn('forms','status') && !isset($row['status'])) {
                        $row['status'] = 'published'; // sesuaikan default projectmu
                    }
                    $this->fillAudit('forms', $row, $defaultUserId); // <<< NEW
                    $forms[] = $row;
                    $formId++;
                }
            }
            DB::table('forms')->insert($forms);

            // ====== Document Templates ======
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
                $this->fillAudit('document_templates', $row, $defaultUserId); // <<< NEW
                $templates[] = $row;
            }
            DB::table('document_templates')->insert($templates);

            // ====== Documents & ACLs (30 hari) ======
            $documents = [];
            $docAcls = [];
            $docId = 1;
            for ($days=29; $days>=0; $days--) {
                $d = Carbon::today()->subDays($days);
                foreach ($deptIds as $deptId) {
                    $count = random_int(0, 5);
                    for ($i=0; $i<$count; $i++) {
                        $tplId = random_int(1, count($templates));
                        $title = "Doc $tplId / ".$d->toDateString()." / ".$deptNames[$deptId-1];
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
                            $row['slug'] = Str::slug("doc-$tplId-".$d->toDateString()."-".$deptId).'-'.$docId;
                        }
                        $this->fillAudit('documents', $row, $defaultUserId); // <<< NEW
                        $documents[] = $row;

                        $userId = random_int(1, 3);
                        $docAcls[] = [
                            'document_id'  => $docId,
                            'user_id'      => $userId,
                            'department_id'=> $deptId,
                            'permission'   => 'read',
                            'created_at'   => $createdAt,
                            'updated_at'   => $createdAt,
                        ];
                        // kalau document_acls juga punya created_by/updated_by:
                        if (Schema::hasColumn('document_acls','created_by')) {
                            $docAcls[count($docAcls)-1]['created_by'] = $defaultUserId;
                        }
                        if (Schema::hasColumn('document_acls','updated_by')) {
                            $docAcls[count($docAcls)-1]['updated_by'] = $defaultUserId;
                        }

                        $docId++;
                    }
                }
            }
            if ($documents) DB::table('documents')->insert($documents);
            if ($docAcls) DB::table('document_acls')->insert($docAcls);

            // ====== Form Entries (30 hari) ======
            $formEntries = [];
            $entryId = 1;
            $allFormIds = array_column($forms, 'id');
            for ($days=29; $days>=0; $days--) {
                $d = Carbon::today()->subDays($days);
                foreach ($allFormIds as $fid) {
                    $count = random_int(0, 25);
                    for ($i=0; $i<$count; $i++) {
                        $userId = random_int(1, 3);
                        $created = $d->copy()->addMinutes(random_int(0, 1439));
                        $row = [
                            'id'         => $entryId,
                            'form_id'    => $fid,
                            'user_id'    => $userId,
                            'created_at' => $created,
                            'updated_at' => $created,
                        ];
                        // jika ada kolom created_by/updated_by di form_entries:
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
