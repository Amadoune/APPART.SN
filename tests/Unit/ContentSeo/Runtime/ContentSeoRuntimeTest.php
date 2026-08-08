<?php

namespace Tests\Unit\ContentSeo\Runtime;

use Appart\Modules\ContentSeo\Application\OwnerSource\ContentSeoOwnerSource;
use Appart\Modules\ContentSeo\Application\OwnerSource\EditorialContentReadResult;
use Appart\Modules\ContentSeo\Application\OwnerSource\OperationalSeoReadResult;
use Appart\Modules\ContentSeo\Application\Runtime\ContentSeoRuntimeAvailability;
use Appart\Modules\ContentSeo\Application\Runtime\DeterministicContentSeoRuntime;
use Appart\Modules\ContentSeo\Application\Runtime\DeterministicContentSeoRuntimeAvailabilityPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ContentSeoRuntimeTest extends TestCase
{
    #[DataProvider('availabilityCases')]
    public function test_policy_reduces_owner_source_results_mechanically(
        EditorialContentReadResult $editorial,
        OperationalSeoReadResult $seo,
        ContentSeoRuntimeAvailability $expected,
    ): void {
        $source = $this->createMock(ContentSeoOwnerSource::class);
        $source->method('readEditorial')->willReturn($editorial);
        $source->method('readOperationalSeo')->willReturn($seo);

        self::assertSame($expected, (new DeterministicContentSeoRuntimeAvailabilityPolicy($source))->inspect());
    }

    public static function availabilityCases(): iterable
    {
        yield 'missing is technically available' => [EditorialContentReadResult::missing(), OperationalSeoReadResult::missing(), ContentSeoRuntimeAvailability::Available];
        yield 'corrupted is fail closed' => [EditorialContentReadResult::corrupted(), OperationalSeoReadResult::missing(), ContentSeoRuntimeAvailability::Corrupted];
        yield 'dependency failure has priority' => [EditorialContentReadResult::corrupted(), OperationalSeoReadResult::dependencyUnavailable(), ContentSeoRuntimeAvailability::DependencyUnavailable];
    }

    public function test_exception_is_dependency_unavailable_and_diagnostics_are_minimal(): void
    {
        $source = $this->createMock(ContentSeoOwnerSource::class);
        $source->method('readEditorial')->willThrowException(new RuntimeException('technical'));
        $policy = new DeterministicContentSeoRuntimeAvailabilityPolicy($source);
        $runtime = new DeterministicContentSeoRuntime($policy);

        self::assertSame(ContentSeoRuntimeAvailability::DependencyUnavailable, $runtime->availability());
        self::assertSame(
            ['runtimeId' => 'content-seo.owner-source', 'version' => 'content-seo-runtime-v1', 'availability' => ContentSeoRuntimeAvailability::DependencyUnavailable],
            get_object_vars($runtime->diagnostics()),
        );
    }
}
