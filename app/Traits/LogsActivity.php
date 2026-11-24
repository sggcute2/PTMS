<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function (Model $model) {
            $modelName = strtoupper(class_basename($model));
            $action = "{$modelName}_CREATED";

            $model->recordActivityLog($action, [
                'attributes' => $model->getAttributesForLogging(),
            ]);
        });

        static::updated(function (Model $model) {
            $dirty = $model->getDirty();
            unset($dirty['updated_at']);

            // Jika perubahan adalah soft delete atau restore, diabaikan di event updated
            if (isset($dirty['deleted_at'])) {
                return;
            }

            if (!empty($dirty)) {
                $modelName = strtoupper(class_basename($model));

                // Khusus jika status yang diperbarui
                if (isset($dirty['status'])) {
                    $action = "{$modelName}_STATUS_UPDATED";
                    $payload = [
                        'field' => 'status',
                        'old' => $model->getOriginal('status'),
                        'new' => $dirty['status'],
                    ];
                } else {
                    $action = "{$modelName}_UPDATED";
                    $old = [];
                    $new = [];
                    foreach ($dirty as $key => $newValue) {
                        $old[$key] = $model->getOriginal($key);
                        $new[$key] = $newValue;
                    }
                    $payload = [
                        'old' => $old,
                        'new' => $new,
                    ];
                }

                $model->recordActivityLog($action, $payload);
            }
        });

        static::deleted(function (Model $model) {
            $modelName = strtoupper(class_basename($model));
            $action = method_exists($model, 'isForceDeleting') && $model->isForceDeleting()
                ? "{$modelName}_FORCE_DELETED"
                : "{$modelName}_DELETED";

            $model->recordActivityLog($action, [
                'attributes' => $model->getAttributesForLogging(),
            ]);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                $modelName = strtoupper(class_basename($model));
                $action = "{$modelName}_RESTORED";

                $model->recordActivityLog($action, [
                    'attributes' => $model->getAttributesForLogging(),
                ]);
            });
        }
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'subject')->latest();
    }

    public function recordActivityLog(string $action, array $payload = []): ?ActivityLog
    {
        // Tentukan user_id: bisa dari Auth, atau relasi project user_id
        $userId = Auth::id() ?? ($this->user_id ?? ($this->project?->user_id ?? null));

        if (!$userId) {
            return null;
        }

        return ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => get_class($this),
            'subject_id' => $this->getKey(),
            'payload' => $payload,
        ]);
    }

    protected function getAttributesForLogging(): array
    {
        $attributes = $this->attributesToArray();
        unset($attributes['created_at'], $attributes['updated_at']);
        return $attributes;
    }
}
