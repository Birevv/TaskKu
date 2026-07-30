<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->index(
                ['workspace_id', 'deleted_at', 'archived_at', 'due_at'],
                'tasks_workspace_visibility_due_idx',
            );
            $table->index(
                ['workspace_id', 'deleted_at', 'archived_at', 'status', 'due_at'],
                'tasks_workspace_status_due_idx',
            );
            $table->index(
                ['workspace_id', 'deleted_at', 'archived_at', 'status', 'completed_at'],
                'tasks_workspace_completed_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropIndex('tasks_workspace_visibility_due_idx');
            $table->dropIndex('tasks_workspace_status_due_idx');
            $table->dropIndex('tasks_workspace_completed_idx');
        });
    }
};
