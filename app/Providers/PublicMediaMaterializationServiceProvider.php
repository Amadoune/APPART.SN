<?php

namespace App\Providers;

use App\Application\PublicMediaMaterialization\Contract\AffectedPublicMediaListingReaderV1;
use App\Application\PublicMediaMaterialization\Contract\CatchUpPublicMediaDecisionV2;
use App\Application\PublicMediaMaterialization\Contract\MaterializePublicMediaDecisionV2;
use App\Application\PublicMediaMaterialization\Contract\PublicMediaOwnerSourceReaderV2;
use App\Application\PublicMediaMaterialization\DeterministicPublicMediaDecisionMaterializerV2;
use App\Application\PublicMediaSource\Contract\PublicMediaDecisionReader;
use App\Application\PublicMediaSource\Contract\PublicMediaDecisionWriter;
use App\Infrastructure\PublicMediaMaterialization\PostgreSqlAffectedPublicMediaListingReader;
use App\Infrastructure\PublicMediaMaterialization\PostgreSqlPublicMediaOwnerSourceReaderV2;
use App\Infrastructure\PublicMediaSource\PostgreSql\PostgreSqlPublicMediaMapper;
use App\Infrastructure\PublicMediaSource\PostgreSql\PostgreSqlPublicMediaReader;
use App\Infrastructure\PublicMediaSource\PostgreSql\PostgreSqlPublicMediaWriter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class PublicMediaMaterializationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PostgreSqlPublicMediaMapper::class, static fn (Application $app): PostgreSqlPublicMediaMapper => new PostgreSqlPublicMediaMapper((string) $app['config']->get('app.url')));
        $this->app->bind(PublicMediaDecisionReader::class, PostgreSqlPublicMediaReader::class);
        $this->app->bind(PublicMediaDecisionWriter::class, PostgreSqlPublicMediaWriter::class);
        $this->app->bind(PublicMediaOwnerSourceReaderV2::class, PostgreSqlPublicMediaOwnerSourceReaderV2::class);
        $this->app->bind(AffectedPublicMediaListingReaderV1::class, PostgreSqlAffectedPublicMediaListingReader::class);
        $this->app->singleton(DeterministicPublicMediaDecisionMaterializerV2::class);
        $this->app->alias(DeterministicPublicMediaDecisionMaterializerV2::class, MaterializePublicMediaDecisionV2::class);
        $this->app->alias(DeterministicPublicMediaDecisionMaterializerV2::class, CatchUpPublicMediaDecisionV2::class);
    }
}
