<?php

namespace Tests\Unit\ContentSeo\PublicRead;

use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoObservedAt;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoPublicResourceKey;
use Appart\Modules\ContentSeo\Application\PublicRead\Contract\EditorialContentReaderV1;
use Appart\Modules\ContentSeo\Application\PublicRead\Contract\OperationalSeoReaderV1;
use Appart\Modules\ContentSeo\Application\PublicRead\EditorialContentResultV1;
use Appart\Modules\ContentSeo\Application\PublicRead\EditorialContentStatusV1;
use Appart\Modules\ContentSeo\Application\PublicRead\OperationalSeoResultV1;
use Appart\Modules\ContentSeo\Application\PublicRead\OperationalSeoStatusV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class ContentSeoPublicReadContractsTest extends TestCase
{
    #[Test]
    public function catalogues_are_closed_and_exact(): void
    {
        self::assertSame(
            ['published', 'unpublished', 'missing', 'corrupted', 'dependency_unavailable'],
            array_column(EditorialContentStatusV1::cases(), 'value'),
        );
        self::assertSame(
            ['indexable', 'no_index', 'missing', 'corrupted', 'dependency_unavailable'],
            array_column(OperationalSeoStatusV1::cases(), 'value'),
        );
    }

    #[Test]
    public function results_expose_only_the_closed_status(): void
    {
        self::assertSame(EditorialContentStatusV1::Published, EditorialContentResultV1::published()->status);
        self::assertSame(EditorialContentStatusV1::Unpublished, EditorialContentResultV1::unpublished()->status);
        self::assertSame(OperationalSeoStatusV1::Indexable, OperationalSeoResultV1::indexable()->status);
        self::assertSame(OperationalSeoStatusV1::NoIndex, OperationalSeoResultV1::noIndex()->status);
        self::assertSame(['status'], array_keys(get_object_vars(EditorialContentResultV1::missing())));
        self::assertSame(['status'], array_keys(get_object_vars(OperationalSeoResultV1::missing())));
    }

    #[Test]
    public function value_objects_are_explicit_canonical_and_utc(): void
    {
        $resource = new ContentSeoPublicResourceKey('editorial/guides/dakar');
        $observedAt = new ContentSeoObservedAt(new DateTimeImmutable('2026-08-02 18:19:20.123456+02:00'));

        self::assertSame('editorial/guides/dakar', $resource->canonical());
        self::assertSame('UTC', $observedAt->value->getTimezone()->getName());
        self::assertSame('2026-08-02T16:19:20.123456Z', $observedAt->canonical());
        self::assertFalse(method_exists($observedAt, 'now'));
    }

    #[Test]
    public function interfaces_share_the_public_identity_and_explicit_observation(): void
    {
        foreach ([
            EditorialContentReaderV1::class => EditorialContentResultV1::class,
            OperationalSeoReaderV1::class => OperationalSeoResultV1::class,
        ] as $interface => $result) {
            $method = new ReflectionMethod($interface, 'read');
            self::assertTrue($method->getDeclaringClass()->isInterface());
            self::assertSame(
                [ContentSeoPublicResourceKey::class, ContentSeoObservedAt::class],
                array_map(static fn ($parameter): string => (string) $parameter->getType(), $method->getParameters()),
            );
            self::assertSame($result, (string) $method->getReturnType());
        }
    }
}
