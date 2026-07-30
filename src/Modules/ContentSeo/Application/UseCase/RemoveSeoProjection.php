<?php

namespace Appart\Modules\ContentSeo\Application\UseCase;

use Appart\Modules\ContentSeo\Application\Contract\SeoProjectionRegistry;
use Appart\Modules\ContentSeo\Domain\Exception\InvalidSeoValue;
use Appart\Modules\ContentSeo\Domain\Exception\SeoSourceUnavailable;
use Appart\Modules\ContentSeo\Domain\Exception\SeoViolation;
use Appart\Modules\ContentSeo\Domain\Policy\SeoFreshnessPolicy;
use Appart\Modules\ContentSeo\Domain\Policy\SeoGenerationPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoFailSafeReason;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionId;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionState;
use DateTimeImmutable;

final readonly class RemoveSeoProjection extends SeoUseCase
{
    public function __construct(SeoProjectionRegistry $projections, private SeoSources $sources, private SeoGenerationPolicy $policy, private SeoFreshnessPolicy $freshness)
    {
        parent::__construct($projections);
    }

    public function execute(SeoProjectionId $id, DateTimeImmutable $at): void
    {
        [$projection, $version] = $this->load($id);
        try {
            $material = $this->policy->generate(...$this->sources->load($projection->listingId()));
            if ($material->state !== SeoProjectionState::Removed) {
                throw new SeoViolation('Removal must be derived from non-publishable sources.');
            }
            $projection->update($material, $at, $this->freshness);
        } catch (SeoSourceUnavailable) {
            $projection->failSafe(SeoFailSafeReason::SourceAbsent, $at);
        } catch (InvalidSeoValue) {
            $projection->failSafe(SeoFailSafeReason::InvalidContent, $at);
        }
        $this->projections->save($projection, $version);
    }
}
