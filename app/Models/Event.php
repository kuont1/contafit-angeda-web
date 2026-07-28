<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'start_at',
        'end_at',
        'color',
        'status',
        'is_recurring',
        'recurrence_frequency',
        'recurrence_parent_id',
        'reminder_minutes_before',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'completed_at' => 'datetime',
            'is_recurring' => 'boolean',
            'reminder_minutes_before' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function recurrenceParent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'recurrence_parent_id');
    }
}
