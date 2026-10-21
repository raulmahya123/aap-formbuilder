<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nickname')) {
                $table->string('nickname', 100)->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'birth_place')) {
                $table->string('birth_place', 100)->nullable()->after('birth_date');
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->enum('gender', ['L', 'P'])->nullable()->after('birth_place');
            }
            if (!Schema::hasColumn('users', 'whatsapp')) {
                $table->string('whatsapp', 30)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('whatsapp');
            }
            if (!Schema::hasColumn('users', 'province')) {
                $table->string('province', 100)->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city', 100)->nullable()->after('province');
            }
            if (!Schema::hasColumn('users', 'district')) {
                $table->string('district', 100)->nullable()->after('city');
            }
            if (!Schema::hasColumn('users', 'postal_code')) {
                $table->string('postal_code', 10)->nullable()->after('district');
            }
            if (!Schema::hasColumn('users', 'emergency_contact_name')) {
                $table->string('emergency_contact_name', 150)->nullable()->after('postal_code');
            }
            if (!Schema::hasColumn('users', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone', 30)->nullable()->after('emergency_contact_name');
            }
            if (!Schema::hasColumn('users', 'position')) {
                $table->string('position', 100)->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'division')) {
                $table->string('division', 100)->nullable()->after('position');
            }
            if (!Schema::hasColumn('users', 'joined_date')) {
                $table->date('joined_date')->nullable()->after('division');
            }
            if (!Schema::hasColumn('users', 'employee_status')) {
                $table->string('employee_status', 50)->default('PKWT')->after('joined_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nickname',
                'birth_place',
                'gender',
                'whatsapp',
                'address',
                'province',
                'city',
                'district',
                'postal_code',
                'emergency_contact_name',
                'emergency_contact_phone',
                'position',
                'division',
                'joined_date',
                'employee_status',
            ]);
        });
    }
};
