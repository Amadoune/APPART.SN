<?php

namespace Tests\Feature;

use App\Application\IdentityAccessEventOutbox\Contract\IdentityAccessOutboxReader;
use App\Application\IdentityAccessEventOutbox\Contract\IdentityAccessOutboxWriter;
use App\Infrastructure\IdentityAccessEventOutbox\PostgreSql\PostgreSqlIdentityAccessOutbox;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class IdentityAccessEventOutboxRuntimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->singleton(PDO::class, static fn (): PDO => new PDO('sqlite::memory:'));
    }

    #[Test]
    public function writer_and_reader_are_lazy_aliases_of_one_owner(): void
    {
        self::assertFalse($this->app->resolved(IdentityAccessOutboxWriter::class));
        self::assertFalse($this->app->resolved(IdentityAccessOutboxReader::class));

        $writer = $this->app->make(IdentityAccessOutboxWriter::class);
        $reader = $this->app->make(IdentityAccessOutboxReader::class);

        self::assertInstanceOf(PostgreSqlIdentityAccessOutbox::class, $writer);
        self::assertSame($writer, $reader);
        self::assertSame($writer, $this->app->make(PostgreSqlIdentityAccessOutbox::class));
    }
}
