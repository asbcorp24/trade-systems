<?php

namespace App\Support;

use App\Models\AuditLog;

class Audit
{
    public static function log(string $event, $model = null, ?string $description = null, array $properties = []): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => $model ? get_class($model) : null,
            'auditable_id' => $model->id ?? null,
            'description' => $description,
            'properties' => $properties ?: null,
        ]);
    }
}
