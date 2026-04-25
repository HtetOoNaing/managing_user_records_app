<?php

namespace App\Jobs;

use App\Models\UserActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WriteUserActivityLog implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const EVENT_USER_CREATED = 'USER_CREATED';
    public const EVENT_USER_UPDATED = 'USER_UPDATED';
    public const EVENT_USER_DELETED = 'USER_DELETED';
    public const EVENT_USER_LOGIN = 'USER_LOGIN';

    public int $tries = 3;

    public array $backoff = [5, 15, 30];

    public function __construct(
        public int $userId,
        public string $event,
        public array $data,
        public ?string $idempotencyKey = null,
    ) {
        $this->idempotencyKey ??= sha1(json_encode([
            'user_id' => $this->userId,
            'event' => $this->event,
            'data' => $this->data,
        ], JSON_THROW_ON_ERROR));
    }

    public function handle(): void
    {
        UserActivityLog::query()->updateOrCreate(
            ['idempotency_key' => $this->idempotencyKey],
            [
                'user_id' => $this->userId,
                'event' => $this->event,
                'data' => $this->sanitizeData($this->data),
            ],
        );
    }

    private function sanitizeData(array $data): array
    {
        unset(
            $data['password'],
            $data['password_hash'],
            $data['token'],
            $data['tokens'],
        );

        return $data;
    }
}
