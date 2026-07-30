<?php

namespace Appart\Modules\ModerationReports\Application\QueueOwnerReadSource;

final readonly class ModerationQueueReadFilterV1
{
    public function __construct(
        public ModerationQueueReadStateV1 $state,
        public ?string $category = null,
    ) {}

    public function checksum(): string
    {
        return hash('sha256', json_encode([
            'category' => $this->category,
            'state' => $this->state->value,
            'version' => 1,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }
}
