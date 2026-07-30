<?php

namespace Tests\Unit\ModerationQueueOwnerReadSource;

use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadFilterV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadPageV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadResultV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadStateV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadStatusV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\QueueOwnerReadSource\ModerationQueueCursorCodecV1;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\QueueOwnerReadSource\ModerationQueueCursorPositionV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationQueueOwnerReadSourceContractTest extends TestCase
{
    #[Test]
    public function results_are_closed_and_expose_a_page_only_when_available(): void
    {
        $page = new ModerationQueueReadPageV1([], null);

        self::assertSame(ModerationQueueReadStatusV1::PageAvailable, ModerationQueueReadResultV1::page($page)->status);
        self::assertSame($page, ModerationQueueReadResultV1::page($page)->page);
        self::assertSame(ModerationQueueReadStatusV1::Empty, ModerationQueueReadResultV1::empty()->status);
        self::assertNull(ModerationQueueReadResultV1::empty()->page);
        self::assertSame(ModerationQueueReadStatusV1::InvalidCursor, ModerationQueueReadResultV1::invalidCursor()->status);
        self::assertSame(ModerationQueueReadStatusV1::Corrupted, ModerationQueueReadResultV1::corrupted()->status);
        self::assertSame(ModerationQueueReadStatusV1::DependencyUnavailable, ModerationQueueReadResultV1::dependencyUnavailable()->status);
    }

    #[Test]
    public function cursor_is_opaque_filter_bound_and_tamper_evident(): void
    {
        $codec = new ModerationQueueCursorCodecV1('unit-test-integrity-key');
        $filter = new ModerationQueueReadFilterV1(ModerationQueueReadStateV1::Available, 'fraud');
        $position = new ModerationQueueCursorPositionV1(
            80,
            new DateTimeImmutable('2026-07-30T10:00:00.000000+00:00'),
            $this->id(1),
        );
        $cursor = $codec->encode($position, $filter->checksum());

        self::assertStringNotContainsString($this->id(1), $cursor);
        self::assertEquals($position, $codec->decode($cursor, $filter->checksum()));
        self::assertNull($codec->decode($cursor, (new ModerationQueueReadFilterV1(ModerationQueueReadStateV1::Available, 'spam'))->checksum()));
        self::assertNull($codec->decode($cursor.'x', $filter->checksum()));
    }

    private function id(int $suffix): string
    {
        return sprintf('53b10000-0000-4000-8000-%012d', $suffix);
    }
}
