<?php

namespace Tests\Unit\MediaIngestionEvent;

use App\Application\MediaIngestionEventOutbox\MediaIngestionOutboxWriteResult;
use PHPUnit\Framework\TestCase;

final class MediaIngestionEventOutboxContractTest extends TestCase
{
    public function test_write_results_are_closed_and_stable(): void
    {
        self::assertSame(
            ['applied', 'already_applied', 'divergent_message'],
            array_column(MediaIngestionOutboxWriteResult::cases(), 'value'),
        );
    }
}
