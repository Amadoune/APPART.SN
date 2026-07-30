<?php

namespace Appart\Modules\ContentSeo\Application\UseCase;

use Appart\Modules\ContentSeo\Application\Contract\SeoProjectionRegistry;
use Appart\Modules\ContentSeo\Domain\Policy\CanonicalHistoryPolicy;
use Appart\Modules\ContentSeo\Domain\Policy\CanonicalPolicy;
use Appart\Modules\ContentSeo\Domain\ValueObject\SeoProjectionId;
use DateTimeImmutable;

final readonly class ChangeCanonical extends SeoUseCase
{
    public function __construct(SeoProjectionRegistry $projections, private CanonicalPolicy $policy, private CanonicalHistoryPolicy $history)
    {
        parent::__construct($projections);
    }

    public function execute(SeoProjectionId $id, string $canonicalPath, DateTimeImmutable $at): void
    {
        [$projection, $version] = $this->load($id);
        $projection->changeCanonical($this->policy->fromPath($canonicalPath), $at, $this->history);
        $this->projections->save($projection, $version);
    }
}
