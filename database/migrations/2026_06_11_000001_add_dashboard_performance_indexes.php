<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndex('form_entries', ['created_at'], 'form_entries_created_at_idx');
        $this->addIndex('form_entries', ['form_id', 'created_at'], 'form_entries_form_created_idx');
        $this->addIndex('form_entries', ['form_id', 'user_id', 'created_at'], 'form_entries_form_user_created_idx');

        $this->addIndex('forms', ['department_id', 'is_active'], 'forms_department_active_idx');
        $this->addIndex('forms', ['department_id', 'id'], 'forms_department_id_idx');

        $this->addIndex('documents', ['department_id', 'created_at'], 'documents_department_created_idx');
        $this->addIndex('documents', ['template_id', 'created_at'], 'documents_template_created_idx');

        $this->addIndex('document_templates', ['updated_at'], 'document_templates_updated_at_idx');

        $this->addIndex('document_acls', ['department_id', 'created_at'], 'document_acls_department_created_idx');
        $this->addIndex('document_acls', ['document_id', 'created_at'], 'document_acls_document_created_idx');
    }

    public function down(): void
    {
        $this->dropIndex('document_acls', 'document_acls_document_created_idx');
        $this->dropIndex('document_acls', 'document_acls_department_created_idx');
        $this->dropIndex('document_templates', 'document_templates_updated_at_idx');
        $this->dropIndex('documents', 'documents_template_created_idx');
        $this->dropIndex('documents', 'documents_department_created_idx');
        $this->dropIndex('forms', 'forms_department_id_idx');
        $this->dropIndex('forms', 'forms_department_active_idx');
        $this->dropIndex('form_entries', 'form_entries_form_user_created_idx');
        $this->dropIndex('form_entries', 'form_entries_form_created_idx');
        $this->dropIndex('form_entries', 'form_entries_created_at_idx');
    }

    private function addIndex(string $table, array $columns, string $name): void
    {
        if (! Schema::hasTable($table) || $this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $name) {
            $table->index($columns, $name);
        });
    }

    private function dropIndex(string $table, string $name): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($name) {
            $table->dropIndex($name);
        });
    }

    private function indexExists(string $table, string $name): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $name)
            ->exists();
    }
};
