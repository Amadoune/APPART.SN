<?php

namespace Appart\Modules\ModerationReports\Application\ReportOwnerReadSource;

final readonly class ModerationReportOwnerReadResult
{
    private function __construct(
        public ModerationReportOwnerReadStatus $status,
        public ?ModerationReportOwnerReadState $state,
    ) {}

    public static function found(ModerationReportOwnerReadState $state): self
    {
        return new self(ModerationReportOwnerReadStatus::Found, $state);
    }

    public static function missing(): self
    {
        return new self(ModerationReportOwnerReadStatus::Missing, null);
    }

    public static function corrupted(): self
    {
        return new self(ModerationReportOwnerReadStatus::Corrupted, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(ModerationReportOwnerReadStatus::DependencyUnavailable, null);
    }
}
