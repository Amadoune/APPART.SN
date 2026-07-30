<?php

namespace Tests\Feature;

use App\Application\MediaIngestionEventIntegration\MediaIngestionAtomicDelivery;
use App\Application\MediaIngestionEventOutbox\Contract\MediaIngestionOutboxReader;
use App\Application\MediaIngestionEventOutbox\Contract\MediaIngestionOutboxWriter;
use App\Infrastructure\MediaIngestionEventOutbox\PostgreSql\PostgreSqlMediaIngestionOutbox;
use PDO;
use Tests\TestCase;

final class MediaIngestionEventOutboxRuntimeTest extends TestCase
{
    public function test_outbox_graph_is_lazy_owner_scoped_and_singleton(): void
    {
        $this->app->instance(PDO::class, $this->createMock(PDO::class));

        foreach ([
            MediaIngestionOutboxWriter::class,
            MediaIngestionOutboxReader::class,
            MediaIngestionAtomicDelivery::class,
        ] as $contract) {
            self::assertFalse($this->app->resolved($contract));
        }

        $writer = $this->app->make(MediaIngestionOutboxWriter::class);
        self::assertInstanceOf(PostgreSqlMediaIngestionOutbox::class, $writer);
        self::assertSame($writer, $this->app->make(MediaIngestionOutboxReader::class));
        self::assertSame($writer, $this->app->make(PostgreSqlMediaIngestionOutbox::class));
        self::assertSame(
            $this->app->make(MediaIngestionAtomicDelivery::class),
            $this->app->make(MediaIngestionAtomicDelivery::class),
        );
    }
}
