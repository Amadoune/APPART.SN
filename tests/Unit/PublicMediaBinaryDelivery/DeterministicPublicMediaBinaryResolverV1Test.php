<?php

namespace Tests\Unit\PublicMediaBinaryDelivery;

use App\Application\PublicMediaBinaryDelivery\Contract\PublicMediaBinaryContentReader;
use App\Application\PublicMediaBinaryDelivery\Contract\PublicMediaBinaryOwnerSourceReader;
use App\Application\PublicMediaBinaryDelivery\DeterministicPublicMediaBinaryResolverV1;
use App\Application\PublicMediaBinaryDelivery\PublicMediaBinaryContentResult;
use App\Application\PublicMediaBinaryDelivery\PublicMediaBinaryContentStatus;
use App\Application\PublicMediaBinaryDelivery\PublicMediaBinarySource;
use App\Application\PublicMediaBinaryDelivery\PublicMediaBinarySourceResult;
use App\Application\PublicMediaBinaryDelivery\PublicMediaBinarySourceStatus;
use App\Application\PublicMediaBinaryDelivery\PublicMediaBinaryStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DeterministicPublicMediaBinaryResolverV1Test extends TestCase
{
    public const MEDIA = '1586b2bc-48ab-57b7-948a-9d9a19813ca8';

    public function test_ready_published_attached_asset_is_resolved_without_exposing_storage_identity(): void
    {
        $binary = 'real-image';
        $stream = fopen('php://temp', 'w+b');
        self::assertIsResource($stream);
        fwrite($stream, $binary);
        rewind($stream);
        $content = new class($stream) implements PublicMediaBinaryContentReader
        {
            public function __construct(private mixed $stream) {}

            public function read(string $ownerId, string $mediaId, string $checksum, int $bytes): PublicMediaBinaryContentResult
            {
                TestCase::assertSame('owner-id', $ownerId);
                TestCase::assertSame(DeterministicPublicMediaBinaryResolverV1Test::MEDIA, $mediaId);

                return new PublicMediaBinaryContentResult(PublicMediaBinaryContentStatus::Found, $this->stream);
            }
        };

        $result = (new DeterministicPublicMediaBinaryResolverV1($this->source(), $content))->resolve(self::MEDIA, 2);

        self::assertSame(PublicMediaBinaryStatus::Found, $result->status);
        self::assertSame('image/jpeg', $result->contentType);
        self::assertSame(strlen($binary), $result->bytes);
        self::assertIsResource($result->stream);
        fclose($result->stream);
    }

    /** @param array<string, bool|int|string> $changes */
    #[DataProvider('notFoundCases')]
    public function test_non_public_facts_and_wrong_revision_are_uniformly_not_found(array $changes, int $requestedVersion): void
    {
        $result = (new DeterministicPublicMediaBinaryResolverV1(
            $this->source($changes),
            new class implements PublicMediaBinaryContentReader
            {
                public function read(string $ownerId, string $mediaId, string $checksum, int $bytes): PublicMediaBinaryContentResult
                {
                    TestCase::fail('Private storage must not be read for an ineligible media.');
                }
            },
        ))->resolve(self::MEDIA, $requestedVersion);

        self::assertSame(PublicMediaBinaryStatus::NotFound, $result->status);
    }

    /** @return iterable<string, array{array<string, bool|int|string>, int}> */
    public static function notFoundCases(): iterable
    {
        yield 'wrong revision' => [[], 1];
        yield 'inactive' => [['mediaStatus' => 'removed'], 2];
        yield 'unready' => [['assetState' => 'quarantined'], 2];
        yield 'unattached' => [['attachmentResult' => 'version_conflict'], 2];
        yield 'private listing' => [['listingPublished' => false], 2];
    }

    public function test_dependency_failure_remains_distinct(): void
    {
        $reader = new class implements PublicMediaBinaryOwnerSourceReader
        {
            public function read(string $mediaId): PublicMediaBinarySourceResult
            {
                return new PublicMediaBinarySourceResult(PublicMediaBinarySourceStatus::DependencyUnavailable);
            }
        };
        $result = (new DeterministicPublicMediaBinaryResolverV1($reader, $this->unusedContent()))->resolve(self::MEDIA, 2);

        self::assertSame(PublicMediaBinaryStatus::DependencyUnavailable, $result->status);
    }

    /** @param array<string, mixed> $changes */
    private function source(array $changes = []): PublicMediaBinaryOwnerSourceReader
    {
        $values = array_replace([
            'mediaStatus' => 'active', 'assetState' => 'ready', 'assetVersion' => 2,
            'attachmentResult' => 'applied', 'listingPublished' => true,
        ], $changes);

        return new class($values) implements PublicMediaBinaryOwnerSourceReader
        {
            /** @param array<string, bool|int|string> $values */
            public function __construct(private array $values) {}

            public function read(string $mediaId): PublicMediaBinarySourceResult
            {
                $checksum = hash('sha256', 'real-image');

                return new PublicMediaBinarySourceResult(PublicMediaBinarySourceStatus::Found, new PublicMediaBinarySource(
                    $mediaId, 'owner-id', $this->values['mediaStatus'], $this->values['assetState'], $this->values['assetVersion'],
                    'image/jpeg', $checksum, strlen('real-image'), $this->values['attachmentResult'], $this->values['listingPublished'],
                ));
            }
        };
    }

    private function unusedContent(): PublicMediaBinaryContentReader
    {
        return new class implements PublicMediaBinaryContentReader
        {
            public function read(string $ownerId, string $mediaId, string $checksum, int $bytes): PublicMediaBinaryContentResult
            {
                TestCase::fail('Content reader must not be called.');
            }
        };
    }
}
