<?php

namespace Tests\Unit\ContentSeoSnapshotMaterialization;

use Appart\Modules\ContentSeo\Application\Materialization\ContentSeoSnapshotIdentityV1;
use Appart\Modules\ContentSeo\Application\Materialization\DeterministicContentSeoCanonicalPathPolicyV1;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use PHPUnit\Framework\TestCase;

final class ContentSeoSnapshotIdentityAndCanonicalPolicyTest extends TestCase
{
    public function test_rc2_identity_and_canonical_path_are_policy_derived_and_stable(): void
    {
        $listing = ListingId::fromString('979cd5aa-ced1-48a1-8adf-8b29c843a0c2');

        self::assertSame('annonces/979cd5aa-ced1-48a1-8adf-8b29c843a0c2', (new DeterministicContentSeoCanonicalPathPolicyV1)->decide($listing));
        self::assertSame('8ae22fb5-5c87-547f-80db-1b41b9d401b4', (new ContentSeoSnapshotIdentityV1)->snapshotId($listing));
    }

    public function test_coherence_identity_is_deterministic_and_fact_sensitive(): void
    {
        $identity = new ContentSeoSnapshotIdentityV1;

        self::assertSame($identity->coherenceId(['listing', 3, 'fact']), $identity->coherenceId(['listing', 3, 'fact']));
        self::assertNotSame($identity->coherenceId(['listing', 3, 'fact']), $identity->coherenceId(['listing', 4, 'fact']));
    }
}
