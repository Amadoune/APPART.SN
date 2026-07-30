<?php

namespace Tests\Unit\Application\PublicProjectionReconciliation\Support;

use App\Application\PublicProjectionReconciliation\Contract\PublicProjectionReconciliationSource;
use App\Application\PublicProjectionReconciliation\PublicProjectionReconciliationObservation;
use App\Application\PublicProjectionReconciliation\PublicProjectionReconciliationPage;

final readonly class FakePublicProjectionReconciliationSource implements PublicProjectionReconciliationSource
{
    /** @param list<PublicProjectionReconciliationObservation> $observations */
    public function __construct(private array $observations) {}

    public function read(?string $checkpoint, int $limit): PublicProjectionReconciliationPage
    {
        $offset = $checkpoint === null ? 0 : (int) $checkpoint;
        $page = array_slice($this->observations, $offset, $limit);
        $next = $offset + count($page);

        return new PublicProjectionReconciliationPage($page, $next < count($this->observations) ? (string) $next : null);
    }
}
