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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictedOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('color', 20)->default('#4f46e5');
            $table->string('visibility', 20)->default('workspace');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['workspace_id', 'slug']);
            $table->index(['workspace_id', 'archived_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
