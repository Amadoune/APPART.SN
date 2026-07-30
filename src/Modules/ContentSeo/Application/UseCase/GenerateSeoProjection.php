<?php

namespace Appart\Modules\ContentSeo\Application\UseCase;

use Appart\Modules\ContentSeo\Application\Contract\SeoProjectionRegistry;
use Appart\Modules\ContentSeo\Domain\Exception\SeoViolation;
use Appart\Modules\ContentSeo\Domain\Model\SeoProjection;
use Appart\Modules\ContentSeo\Domain\Policy\SeoGenerationPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionId;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionState;
use DateTimeImmutable;

final readonly class GenerateSeoProjection
{
    public function __construct(private SeoProjectionRegistry $projections, private SeoSources $sources, private SeoGenerationPolicy $policy) {}

    public function execute(SeoProjectionId $id, ListingId $listingId, DateTimeImmutable $at): SeoProjection
    {
        $material = $this->policy->generate(...$this->sources->load($listingId));
        if ($material->state !== SeoProjectionState::Active) {
            throw new SeoViolation('SEO content cannot be generated for a non-publishable listing.');
        }
        $projection = SeoProjection::generate($id, $listingId, $material, $at);
        $this->projections->add($projection);

        return $projection;
    }
}
