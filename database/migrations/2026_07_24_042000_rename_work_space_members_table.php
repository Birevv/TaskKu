<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Normalize the pivot table name created by the original migration.
     */
    public function up(): void
    {
        if (Schema::hasTable('work_space_members') && ! Schema::hasTable('workspace_members')) {
            Schema::rename('work_space_members', 'workspace_members');
        }
    }

    /**
     * This data-preserving compatibility migration is intentionally irreversible.
     */
    public function down(): void
    {
        //
    }
};
