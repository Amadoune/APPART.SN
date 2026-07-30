<?php

namespace Tests\Unit\AuthoringOperations;

use App\Application\PropertyListingAuthoringOperations\AuthoringOperation;
use App\Application\PropertyListingAuthoringOperations\AuthoringOperationCommand;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AuthoringOperationCommandTest extends TestCase
{
    public function test_checksum_is_deterministic_and_covers_operation_context(): void
    {
        $first = $this->command(['title' => 'A', 'currency' => 'XOF']);
        $reordered = $this->command(['currency' => 'XOF', 'title' => 'A']);
        $different = $this->command(['currency' => 'XOF', 'title' => 'B']);

        self::assertSame($first->checksum(), $reordered->checksum());
        self::assertNotSame($first->checksum(), $different->checksum());
        self::assertSame(64, strlen($first->checksum()));
    }

    public function test_invalid_identity_or_negative_version_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new AuthoringOperationCommand(
            AuthoringOperation::CreateListing,
            'invalid',
            '65000000-0000-4000-8000-000000000001',
            null,
            null,
            -1,
            [],
            new DateTimeImmutable,
        );
    }

    /** @param array<string, mixed> $data */
    private function command(array $data): AuthoringOperationCommand
    {
        return new AuthoringOperationCommand(
            AuthoringOperation::CreateListing,
            '65000000-0000-4000-8000-000000000006',
            '65000000-0000-4000-8000-000000000001',
            '65000000-0000-4000-8000-000000000002',
            '65000000-0000-4000-8000-000000000003',
            0,
            $data,
            new DateTimeImmutable('2026-07-27T18:01:00+00:00'),
        );
    }
}
