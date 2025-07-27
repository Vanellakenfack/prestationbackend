<?php

namespace App\Services;

use App\Models\ModerationLog;

class ModerationLogService
{
    public function log(int $moderatorId, string $action, int $targetId, array $metadata = []): void
    {
        ModerationLog::create([
            'moderator_id' => $moderatorId,
            'action' => $action,
            'target_id' => $targetId,
            'target_type' => $this->resolveTargetType($action),
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    private function resolveTargetType(string $action): string
    {
        return match($action) {
            'service_approval' => Service::class,
            'user_deletion' => User::class,
            default => 'system'
        };
    }
}