<?php

namespace Appart\Modules\ModerationReports\Application\QueueOwnerReadSource;

final readonly class ModerationQueueReadResultV1
{
    private function __construct(
        public ModerationQueueReadStatusV1 $status,
        public ?ModerationQueueReadPageV1 $page,
    ) {}

    public static function page(ModerationQueueReadPageV1 $page): self
    {
        return new self(ModerationQueueReadStatusV1::PageAvailable, $page);
    }

    public static function empty(): self
    {
        return new self(ModerationQueueReadStatusV1::Empty, null);
    }

    public static function invalidCursor(): self
    {
        return new self(ModerationQueueReadStatusV1::InvalidCursor, null);
    }

    public static function corrupted(): self
    {
        return new self(ModerationQueueReadStatusV1::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(ModerationQueueReadStatusV1::DependencyUnavailable, null);
    }
}
