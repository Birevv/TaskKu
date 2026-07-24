<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictedOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('medium');
            $table->string('status')->default('pending');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamp('reminder_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'due_at']);
            $table->index(['workspace_id', 'archived_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
