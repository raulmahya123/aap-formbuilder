<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SampleFormSeeder extends Seeder
{
    public function run(): void
    {
        // pastikan departemen & super admin ada
        $dept = \App\Models\Department::firstOrCreate(
            ['slug' => 'operasional'],
            ['name' => 'Operasional']
        );

        $super = \App\Models\User::where('email','super@aap.test')->first();

        // Skema contoh
        $schema = [
            'fields' => [
                ['label'=>'Nama Lengkap','name'=>'nama','type'=>'text','required'=>true,'rules'=>'string|min:3|max:80'],
                ['label'=>'Email','name'=>'email','type'=>'email','required'=>true,'rules'=>'email'],
                ['label'=>'Tanggal Pengajuan','name'=>'tanggal','type'=>'date','required'=>true],
                ['label'=>'Divisi','name'=>'divisi','type'=>'select','required'=>true,'options'=>[
                    ['ops','Operasional'],['hrga','HRGA'],['keu','Keuangan']
                ]],
                ['label'=>'Keahlian','name'=>'skills','type'=>'checkbox','options'=>[
                    ['go','Go'],['php','PHP'],['js','JavaScript']
                ]],
                ['label'=>'Jenis Kelamin','name'=>'jk','type'=>'radio','required'=>true,'options'=>[
                    ['L','Laki-laki'],['P','Perempuan']
                ]],
                ['label'=>'CV (PDF)','name'=>'cv','type'=>'file','mimes'=>'pdf','max'=>2048,'required'=>true],
                ['label'=>'Catatan','name'=>'catatan','type'=>'textarea','rules'=>'string|max:500'],
            ]
        ];

        // Buat form builder
        \App\Models\Form::firstOrCreate(
            ['slug' => 'form-pengajuan-operator'],
            [
                'department_id' => $dept->id,
                'created_by'    => $super?->id ?? \App\Models\User::first()->id,
                'title'         => 'Form Pengajuan Operator',
                'type'          => 'builder',
                'schema'        => $schema,
                'pdf_path'      => null,
                'is_active'     => true,
            ]
        );

        // (Opsional) Tambah 1 form tipe PDF (tanpa file upload awal)
        \App\Models\Form::firstOrCreate(
            ['slug' => 'panduan-k3-operasional'],
            [
                'department_id' => $dept->id,
                'created_by'    => $super?->id ?? \App\Models\User::first()->id,
                'title'         => 'Panduan K3 (PDF)',
                'type'          => 'pdf',
                'schema'        => null,
                'pdf_path'      => null, // upload nanti via Admin → Edit Form
                'is_active'     => true,
            ]
        );
    }
}
