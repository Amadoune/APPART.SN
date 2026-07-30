<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

use Appart\Modules\ContentSeo\Domain\Exception\InvalidSeoValue;

final readonly class HtmlRobotsDirective
{
    private const ALLOWED = ['index, follow', 'noindex, follow'];

    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        if (! in_array($value, self::ALLOWED, true)) {
            throw InvalidSeoValue::field('html_robots_directive');
        }

        return new self($value);
    }

    public static function fromPolicy(RobotsPolicy $policy): self
    {
        return self::fromString(match ($policy) {
            RobotsPolicy::IndexFollow => 'index, follow',
            RobotsPolicy::NoIndexFollow => 'noindex, follow',
        });
    }
}
