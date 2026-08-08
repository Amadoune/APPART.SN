<?php

namespace Appart\Modules\ContentSeo\Application\PublicRead;

final readonly class OperationalSeoResultV1
{
    private function __construct(public OperationalSeoStatusV1 $status) {}

    public static function indexable(): self
    {
        return new self(OperationalSeoStatusV1::Indexable);
    }

    public static function noIndex(): self
    {
        return new self(OperationalSeoStatusV1::NoIndex);
    }

    public static function missing(): self
    {
        return new self(OperationalSeoStatusV1::Missing);
    }

    public static function corrupted(): self
    {
        return new self(OperationalSeoStatusV1::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(OperationalSeoStatusV1::DependencyUnavailable);
    }
}
