<?php

namespace Appart\Modules\ModerationReports\Application\HttpReadBoundaries;

final readonly class ReadOwnModerationReportResultV1
{
    private function __construct(
        public ReadStatusV1 $status,
        public ?OwnModerationReportViewV1 $report,
    ) {}

    public static function visible(OwnModerationReportViewV1 $report): self
    {
        return new self(ReadStatusV1::Visible, $report);
    }

    public static function notVisible(): self
    {
        return new self(ReadStatusV1::NotVisible, null);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(ReadStatusV1::DependencyUnavailable, null);
    }
}
