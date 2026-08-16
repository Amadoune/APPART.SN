<?php

namespace App\Providers;

use App\Infrastructure\SearchDecisionMaterialization\PostgreSqlPublicSearchMaterializationSourceReaderV1;
use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionReader;
use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionWriter;
use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\CatchUpPublicSearchDecisionV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\MaterializePublicSearchDecisionV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\PublicSearchMaterializationSourceReaderV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\PublicSearchRankingPolicyV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\DeterministicCatchUpPublicSearchDecisionV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\DeterministicPublicSearchDecisionMaterializerV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\DeterministicPublicSearchRankingPolicyV1;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchDecisionReader;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchDecisionWriter;
use Illuminate\Support\ServiceProvider;

final class PublicSearchDecisionMaterializationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeterministicPublicSearchRankingPolicyV1::class);
        $this->app->alias(DeterministicPublicSearchRankingPolicyV1::class, PublicSearchRankingPolicyV1::class);
        $this->app->singleton(PostgreSqlPublicSearchMaterializationSourceReaderV1::class);
        $this->app->alias(PostgreSqlPublicSearchMaterializationSourceReaderV1::class, PublicSearchMaterializationSourceReaderV1::class);
        $this->app->singleton(PostgreSqlSearchDecisionReader::class);
        $this->app->alias(PostgreSqlSearchDecisionReader::class, SearchDecisionReader::class);
        $this->app->singleton(PostgreSqlSearchDecisionWriter::class);
        $this->app->alias(PostgreSqlSearchDecisionWriter::class, SearchDecisionWriter::class);
        $this->app->singleton(DeterministicPublicSearchDecisionMaterializerV1::class);
        $this->app->alias(DeterministicPublicSearchDecisionMaterializerV1::class, MaterializePublicSearchDecisionV1::class);
        $this->app->singleton(DeterministicCatchUpPublicSearchDecisionV1::class);
        $this->app->alias(DeterministicCatchUpPublicSearchDecisionV1::class, CatchUpPublicSearchDecisionV1::class);
    }
}
