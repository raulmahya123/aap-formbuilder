<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('document_signatures', function (Blueprint $t) {
            $t->foreignId('signed_by_user_id')->nullable()
              ->constrained('users')->nullOnDelete()->after('order');
            $t->timestamp('signed_at')->nullable()->after('signed_by_user_id');
            $t->string('signed_image_path')->nullable()->after('signed_at'); // hasil kanvas
            $t->string('signed_hash')->nullable()->after('signed_image_path'); // checksum/verification
        });
    }

    public function down(): void {
        Schema::table('document_signatures', function (Blueprint $t) {
            // drop FK + kolom
            $t->dropConstrainedForeignId('signed_by_user_id');
            $t->dropColumn(['signed_at','signed_image_path','signed_hash']);
        });
    }
};
