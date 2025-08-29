<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('template_id')->nullable()->constrained('document_templates')->nullOnDelete();

            $t->string('doc_no')->nullable();       // ex: PLT-SOP-003
            $t->string('dept_code')->nullable();    // ENG, MIN, PLT, ...
            $t->string('doc_type')->nullable();     // SOP, IK, ST, MA, FM, JSA, KT
            $t->string('project_code')->nullable(); // 3 huruf opsional
            $t->unsignedInteger('revision_no')->default(0);
            $t->date('effective_date')->nullable();
            $t->string('title');

            $t->enum('controlled_status',['controlled','uncontrolled','obsolete'])->default('controlled');
            $t->enum('class',['I','II','III','IV'])->nullable();

            $t->json('header_config')->nullable();
            $t->json('footer_config')->nullable();
            $t->json('signature_config')->nullable();
            $t->json('sections')->nullable();  // [{key,label,html}]
            $t->json('meta')->nullable();

            $t->foreignId('owner_id')->constrained('users'); // pembuat
            $t->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();

            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('documents'); }
};
