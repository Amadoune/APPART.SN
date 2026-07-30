<?php

namespace Tests\Unit\MediaIngestionPersistence;

use Appart\Modules\Media\Infrastructure\Persistence\MediaIngestionStateMapper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class MediaIngestionStateMapperTest extends TestCase
{
    #[Test]
    public function it_reconstructs_all_owner_states_deterministically(): void
    {
        $mapper = new MediaIngestionStateMapper;
        $row = [
            'aggregate_id' => '58000000-0000-4000-8000-000000000001',
            'state' => 'reserved',
            'version' => 1,
            'last_intent_id' => '58000000-0000-4000-8001-000000000001',
            'last_intent_checksum' => hash('sha256', 'intent'),
            'payload' => '{"bytes":1024,"owner":"opaque"}',
        ];

        foreach (['upload', 'asset', 'processing', 'quota'] as $method) {
            $state = $mapper->{$method}($row);
            self::assertSame($row['aggregate_id'], $state->id);
            self::assertSame(1, $state->version);
            self::assertSame(['bytes' => 1024, 'owner' => 'opaque'], $state->payload);
        }
    }

    #[Test]
    public function it_rejects_corrupt_payload_and_checksum(): void
    {
        $mapper = new MediaIngestionStateMapper;
        $this->expectException(UnexpectedValueException::class);
        $mapper->upload([
            'aggregate_id' => 'id',
            'state' => 'reserved',
            'version' => 1,
            'last_intent_id' => 'intent',
            'last_intent_checksum' => 'invalid',
            'payload' => '{}',
        ]);
    }
}
