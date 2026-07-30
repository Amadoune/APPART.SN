<?php

namespace Tests\Unit\Application;

use Appart\Modules\IdentityAccess\Infrastructure\Persistence\IdentityAccessCompletionPersistenceMapper;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IdentityAccessCompletionPersistenceMapperTest extends TestCase
{
    #[Test]
    public function it_maps_only_the_owner_allow_list(): void
    {
        $snapshot = (new IdentityAccessCompletionPersistenceMapper)->snapshot([
            'account_id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'version' => 2,
            'last_intent_id' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            'last_intent_checksum' => str_repeat('a', 64),
            'state' => 'Closed',
            'unowned' => 'must-not-cross',
        ], 'account_id', ['state']);

        self::assertSame(['state' => 'Closed'], $snapshot->values);
        self::assertSame(2, $snapshot->version);
    }

    #[Test]
    public function it_rejects_a_corrupted_row(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new IdentityAccessCompletionPersistenceMapper)->snapshot([], 'account_id', ['state']);
    }
}
