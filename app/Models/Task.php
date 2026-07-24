<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'project_id',
        'created_by',
        'title',
        'description',
        'priority',
        'status',
        'due_at',
        'reminder_at',
        'completed_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'priority' => TaskPriority::class,
            'status' => TaskStatus::class,
            'due_at' => 'datetime',
            'reminder_at' => 'datetime',
            'completed_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignees')->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('archived_at')
            ->whereNotIn('status', [TaskStatus::Completed, TaskStatus::Cancelled]);
    }
}
