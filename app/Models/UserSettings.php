<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'theme',
        'timezone',
        'notify_task_assigned',
        'notify_task_due',
    ];

    protected function casts(): array
    {
        return [
            'notify_task_assigned' => 'boolean',
            'notify_task_due' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
