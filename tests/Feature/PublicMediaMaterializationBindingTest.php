<?php

namespace Tests\Feature;

use App\Application\PublicMediaMaterialization\Contract\AffectedPublicMediaListingReaderV1;
use App\Application\PublicMediaMaterialization\Contract\CatchUpPublicMediaDecisionV2;
use App\Application\PublicMediaMaterialization\Contract\MaterializePublicMediaDecisionV2;
use App\Application\PublicMediaMaterialization\Contract\PublicMediaOwnerSourceReaderV2;
use App\Application\PublicMediaMaterialization\DeterministicPublicMediaDecisionMaterializerV2;
use App\Infrastructure\PublicMediaMaterialization\PostgreSqlAffectedPublicMediaListingReader;
use App\Infrastructure\PublicMediaMaterialization\PostgreSqlPublicMediaOwnerSourceReaderV2;
use Tests\TestCase;

final class PublicMediaMaterializationBindingTest extends TestCase
{
    public function test_owner_scoped_materializer_and_catch_up_bindings_are_productive(): void
    {
        self::assertInstanceOf(DeterministicPublicMediaDecisionMaterializerV2::class, $this->app->make(MaterializePublicMediaDecisionV2::class));
        self::assertSame($this->app->make(MaterializePublicMediaDecisionV2::class), $this->app->make(CatchUpPublicMediaDecisionV2::class));
        self::assertInstanceOf(PostgreSqlPublicMediaOwnerSourceReaderV2::class, $this->app->make(PublicMediaOwnerSourceReaderV2::class));
        self::assertInstanceOf(PostgreSqlAffectedPublicMediaListingReader::class, $this->app->make(AffectedPublicMediaListingReaderV1::class));
    }
}
