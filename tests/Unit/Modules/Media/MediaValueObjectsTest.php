<?php

namespace Tests\Unit\Modules\Media;

use Appart\Modules\Media\Domain\Exception\InvalidMediaValue;
use Appart\Modules\Media\Domain\ValueObject\MediaCaption;
use Appart\Modules\Media\Domain\ValueObject\MediaChecksum;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Appart\Modules\Media\Domain\ValueObject\MediaOrder;
use Appart\Modules\Media\Domain\ValueObject\MediaType;
use Appart\Modules\Media\Domain\ValueObject\PropertyId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MediaValueObjectsTest extends TestCase
{
    public function test_mvp_accepts_images_only(): void
    {
        self::assertSame([MediaType::Image], MediaType::cases());
        self::assertNull(MediaType::tryFrom('video'));
        self::assertNull(MediaType::tryFrom('document'));
    }

    public function test_caption_and_checksum_are_normalized(): void
    {
        self::assertSame('Vue principale', MediaCaption::fromString(' Vue   principale ')->value);
        self::assertSame(str_repeat('a', 64), MediaChecksum::fromSha256(str_repeat('A', 64))->value);
    }

    #[DataProvider('invalidValues')]
    public function test_invalid_values_are_rejected(string $type, mixed $value): void
    {
        $this->expectException(InvalidMediaValue::class);
        match ($type) {
            'collection' => MediaCollectionId::fromString((string) $value), 'media' => MediaId::fromString((string) $value), 'property' => PropertyId::fromString((string) $value), 'checksum' => MediaChecksum::fromSha256((string) $value), 'order' => MediaOrder::fromInt((int) $value), 'caption' => MediaCaption::fromString((string) $value)
        };
    }

    public static function invalidValues(): array
    {
        return [['collection', 'x'], ['media', 'x'], ['property', 'x'], ['checksum', 'abc'], ['order', 0], ['caption', '']];
    }
}
