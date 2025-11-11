<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Identitas perusahaan
            $table->string('code', 16)->unique();      // contoh: ABN, AAP
            $table->string('name');                    // nama brand/operasional
            $table->string('legal_name')->nullable();  // nama badan hukum (PT ...)
            $table->string('slug')->unique();          // untuk URL: abn, aap, dll.
            $table->string('industry')->nullable();    // tambang, logistik, dll.

            // Legal & registrasi
            $table->string('registration_no', 64)->nullable(); // TDP/SIUP/dll (opsional)
            $table->string('npwp', 32)->nullable()->unique();
            $table->string('nib', 32)->nullable()->unique();

            // Kontak & situs
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();

            // Alamat
            $table->string('hq_address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code', 16)->nullable();
            $table->string('country', 2)->default('ID'); // ISO-3166 alpha-2, ex: ID
            $table->json('addresses')->nullable();       // daftar alamat lain (opsional JSON)

            // Preferensi
            $table->string('timezone')->default('Asia/Jakarta');
            $table->string('currency', 3)->default('IDR'); // ISO-4217

            // Branding
            $table->string('logo_path')->nullable(); // path/logo di storage

            // Status
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Index tambahan
            $table->index(['status', 'country']);
            $table->index('industry');

            // Relasi user (opsional, null on delete)
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
