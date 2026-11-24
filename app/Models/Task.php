<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'project_id',
        'title',
        'status',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }

    /**
     * Memeriksa apakah tugas melewati batas waktu (due_date) dan belum selesai (completed)
     */
    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === 'completed' || !$this->due_date) {
            return false;
        }

        return $this->due_date->isPast() && !$this->due_date->isToday();
    }
}
