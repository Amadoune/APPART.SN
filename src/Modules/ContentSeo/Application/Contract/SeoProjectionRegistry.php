<?php

namespace Appart\Modules\ContentSeo\Application\Contract;

use Appart\Modules\ContentSeo\Domain\Model\SeoProjection;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionId;

interface SeoProjectionRegistry
{
    /** Returns a detached projection reconstructed without previously published events. */
    public function find(SeoProjectionId $id): ?SeoProjection;

    public function add(SeoProjection $projection): void;

    public function save(SeoProjection $projection, int $expectedVersion): void;
}
/** Atomically adds a unique projection identity and its canonical reservations. */
/** Saves a clean snapshot conditionally on expectedVersion; no partial canonical reservation is visible on failure. */
