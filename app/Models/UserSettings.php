<?php

namespace App\Models;

use App\Enums\DisplayDensity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'theme',
        'density',
        'timezone',
        'notify_task_assigned',
        'notify_task_due',
    ];

    protected function casts(): array
    {
        return [
            'density' => DisplayDensity::class,
            'notify_task_assigned' => 'boolean',
            'notify_task_due' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
