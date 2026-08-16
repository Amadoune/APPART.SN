<?php

namespace Tests\Feature;

use App\Infrastructure\SearchDecisionMaterialization\PostgreSqlPublicSearchMaterializationSourceReaderV1;
use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionWriter;
use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\CatchUpPublicSearchDecisionV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\MaterializePublicSearchDecisionV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\PublicSearchMaterializationSourceReaderV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\PublicSearchRankingPolicyV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\DeterministicCatchUpPublicSearchDecisionV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\DeterministicPublicSearchDecisionMaterializerV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\DeterministicPublicSearchRankingPolicyV1;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchDecisionWriter;
use Tests\TestCase;

final class PublicSearchDecisionMaterializationBindingTest extends TestCase
{
    public function test_productive_composition_is_bound_as_singletons(): void
    {
        self::assertInstanceOf(DeterministicPublicSearchRankingPolicyV1::class, $this->app->make(PublicSearchRankingPolicyV1::class));
        self::assertInstanceOf(PostgreSqlPublicSearchMaterializationSourceReaderV1::class, $this->app->make(PublicSearchMaterializationSourceReaderV1::class));
        self::assertInstanceOf(PostgreSqlSearchDecisionWriter::class, $this->app->make(SearchDecisionWriter::class));
        self::assertInstanceOf(DeterministicPublicSearchDecisionMaterializerV1::class, $this->app->make(MaterializePublicSearchDecisionV1::class));
        self::assertInstanceOf(DeterministicCatchUpPublicSearchDecisionV1::class, $this->app->make(CatchUpPublicSearchDecisionV1::class));
        self::assertSame($this->app->make(MaterializePublicSearchDecisionV1::class), $this->app->make(MaterializePublicSearchDecisionV1::class));
    }
}
