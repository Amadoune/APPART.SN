<?php

namespace Appart\Modules\ContentSeo\Application\OwnerSource;

use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoObservedAt;
use Appart\Modules\ContentSeo\Application\PublicRead\ContentSeoPublicResourceKey;

interface ContentSeoOwnerSource
{
    public function appendEditorial(EditorialContentRevisionState $revision): EditorialContentWriteResult;

    public function appendOperationalSeo(OperationalSeoRevisionState $revision): OperationalSeoWriteResult;

    public function readEditorial(ContentSeoPublicResourceKey $resource, ContentSeoObservedAt $observedAt): EditorialContentReadResult;

    public function readOperationalSeo(ContentSeoPublicResourceKey $resource, ContentSeoObservedAt $observedAt): OperationalSeoReadResult;

    /** @return list<EditorialContentRevisionState> */
    public function editorialHistory(ContentSeoPublicResourceKey $resource): array;

    /** @return list<OperationalSeoRevisionState> */
    public function operationalSeoHistory(ContentSeoPublicResourceKey $resource): array;
}
