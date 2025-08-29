<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('documents', function (Blueprint $t) {
            $t->string('qr_text')->nullable()->after('meta');
            $t->string('qr_image_path')->nullable()->after('qr_text');
            $t->string('barcode_text')->nullable()->after('qr_image_path');
            $t->string('barcode_image_path')->nullable()->after('barcode_text');
        });
    }

    public function down(): void {
        Schema::table('documents', function (Blueprint $t) {
            $t->dropColumn(['qr_text','qr_image_path','barcode_text','barcode_image_path']);
        });
    }
};
