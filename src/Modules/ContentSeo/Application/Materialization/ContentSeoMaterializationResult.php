<?php

namespace Appart\Modules\ContentSeo\Application\Materialization;

use Appart\Modules\ContentSeo\Application\Snapshot\ContentSeoSourceDecision;

final readonly class ContentSeoMaterializationResult
{
    public function __construct(public ContentSeoMaterializationStatus $status, public ?ContentSeoSourceDecision $snapshot = null) {}
}
