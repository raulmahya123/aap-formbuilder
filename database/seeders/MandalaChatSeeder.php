<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Site, QaThread, User};

class MandalaChatSeeder extends Seeder
{
    public function run(): void
    {
        $firstUser = User::first();
        $adminId = $firstUser ? $firstUser->id : 1;

        $sites = Site::all();
        foreach ($sites as $site) {
            $existing = QaThread::where('site_id', $site->id)->where('type', 'site')->first();
            if (!$existing) {
                QaThread::create([
                    'subject'         => ($site->code ?: 'SITE') . ' - General',
                    'type'            => 'site',
                    'group_type'      => 'site',
                    'description'     => 'Ruang komunikasi umum untuk site ' . ($site->name ?: $site->code),
                    'scope'           => 'public',
                    'site_id'         => $site->id,
                    'company_id'      => $site->company_id,
                    'created_by'      => $adminId,
                    'status'          => 'open',
                    'last_message_at' => now(),
                ]);
            }
        }
    }
}
