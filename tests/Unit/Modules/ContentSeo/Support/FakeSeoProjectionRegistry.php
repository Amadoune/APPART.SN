<?php

namespace Tests\Unit\Modules\ContentSeo\Support;

use Appart\Modules\ContentSeo\Application\Contract\SeoProjectionRegistry;
use Appart\Modules\ContentSeo\Domain\Exception\CanonicalUrlConflict;
use Appart\Modules\ContentSeo\Domain\Exception\ConcurrentSeoModification;
use Appart\Modules\ContentSeo\Domain\Exception\ListingSeoConflict;
use Appart\Modules\ContentSeo\Domain\Exception\SeoProjectionIdentityConflict;
use Appart\Modules\ContentSeo\Domain\Model\SeoProjection;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionId;

final class FakeSeoProjectionRegistry implements SeoProjectionRegistry
{
    /** @var array<string, SeoProjection> */
    private array $items = [];

    /** @var array<string, string> */
    private array $listings = [];

    /** @var array<string, string> */
    private array $canonicals = [];

    private bool $fail = false;

    public function find(SeoProjectionId $id): ?SeoProjection
    {
        return isset($this->items[$id->value]) ? clone $this->items[$id->value] : null;
    }

    public function add(SeoProjection $projection): void
    {
        $this->guardFailure();
        if (isset($this->items[$projection->id()->value])) {
            throw new SeoProjectionIdentityConflict;
        }
        if (isset($this->listings[$projection->listingId()->value])) {
            throw new ListingSeoConflict;
        }
        $canonical = $projection->document()->material->canonical->value;
        if (isset($this->canonicals[$canonical])) {
            throw new CanonicalUrlConflict;
        }
        $this->store($projection);
        $this->listings[$projection->listingId()->value] = $projection->id()->value;
        $this->canonicals[$canonical] = $projection->id()->value;
    }

    public function save(SeoProjection $projection, int $expectedVersion): void
    {
        $this->guardFailure();
        $stored = $this->items[$projection->id()->value] ?? null;
        if ($stored === null || $stored->version() !== $expectedVersion) {
            throw new ConcurrentSeoModification;
        }
        foreach ($projection->canonicalHistory() as $entry) {
            $owner = $this->canonicals[$entry->canonical->value] ?? null;
            if ($owner !== null && $owner !== $projection->id()->value) {
                throw new CanonicalUrlConflict;
            }
        }
        foreach ($projection->canonicalHistory() as $entry) {
            $this->canonicals[$entry->canonical->value] = $projection->id()->value;
        }
        $this->store($projection);
    }

    public function failNextWrite(): void
    {
        $this->fail = true;
    }

    private function guardFailure(): void
    {
        if ($this->fail) {
            $this->fail = false;
            throw new ConcurrentSeoModification;
        }
    }

    private function store(SeoProjection $projection): void
    {
        $snapshot = clone $projection;
        $snapshot->releaseEvents();
        $this->items[$projection->id()->value] = $snapshot;
    }
}
