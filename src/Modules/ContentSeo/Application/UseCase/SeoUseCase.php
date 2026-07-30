<?php

namespace Appart\Modules\ContentSeo\Application\UseCase;

use Appart\Modules\ContentSeo\Application\Contract\SeoProjectionRegistry;
use Appart\Modules\ContentSeo\Domain\Exception\SeoProjectionNotFound;
use Appart\Modules\ContentSeo\Domain\Model\SeoProjection;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionId;

abstract readonly class SeoUseCase
{
    public function __construct(protected SeoProjectionRegistry $projections) {}

    /** @return array{SeoProjection, int} */
    protected function load(SeoProjectionId $id): array
    {
        $projection = $this->projections->find($id) ?? throw new SeoProjectionNotFound;

        return [$projection, $projection->version()];
    }
}
