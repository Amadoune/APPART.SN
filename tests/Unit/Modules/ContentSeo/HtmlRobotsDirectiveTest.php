<?php

namespace Tests\Unit\Modules\ContentSeo;

use Appart\Modules\ContentSeo\Domain\Exception\InvalidSeoValue;
use Appart\Modules\ContentSeo\Domain\ValueObject\HtmlRobotsDirective;
use Appart\Modules\ContentSeo\Domain\ValueObject\RobotsPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HtmlRobotsDirectiveTest extends TestCase
{
    #[DataProvider('policies')]
    public function test_domain_policy_maps_to_the_exact_html_directive(RobotsPolicy $policy, string $expected): void
    {
        self::assertSame($expected, HtmlRobotsDirective::fromPolicy($policy)->value);
    }

    /** @return iterable<string, array{RobotsPolicy, string}> */
    public static function policies(): iterable
    {
        yield 'indexable' => [RobotsPolicy::IndexFollow, 'index, follow'];
        yield 'not indexable' => [RobotsPolicy::NoIndexFollow, 'noindex, follow'];
    }

    public function test_an_arbitrary_html_directive_is_rejected(): void
    {
        $this->expectException(InvalidSeoValue::class);

        HtmlRobotsDirective::fromString('noindex, nofollow');
    }
}
