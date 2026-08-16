<?php

namespace App\Providers;

use App\Infrastructure\ContentSeoSnapshotMaterialization\PostgreSqlContentSeoMaterializationSourceReaderV1;
use Appart\Modules\ContentSeo\Application\Contract\ContentSeoSourceSnapshotReader;
use Appart\Modules\ContentSeo\Application\Contract\ContentSeoSourceSnapshotWriter;
use Appart\Modules\ContentSeo\Application\Materialization\ContentSeoSnapshotIdentityV1;
use Appart\Modules\ContentSeo\Application\Materialization\Contract\CatchUpContentSeoSnapshotV1;
use Appart\Modules\ContentSeo\Application\Materialization\Contract\ContentSeoCanonicalPathPolicyV1;
use Appart\Modules\ContentSeo\Application\Materialization\Contract\ContentSeoMaterializationSourceReaderV1;
use Appart\Modules\ContentSeo\Application\Materialization\Contract\MaterializeContentSeoSnapshotV1;
use Appart\Modules\ContentSeo\Application\Materialization\DeterministicCatchUpContentSeoSnapshotV1;
use Appart\Modules\ContentSeo\Application\Materialization\DeterministicContentSeoCanonicalPathPolicyV1;
use Appart\Modules\ContentSeo\Application\Materialization\DeterministicContentSeoSnapshotMaterializerV1;
use Appart\Modules\ContentSeo\Domain\Policy\CanonicalPolicy;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\ContentSeoSourceSnapshotMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlContentSeoSourceSnapshotReader;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlContentSeoSourceSnapshotWriter;
use Illuminate\Support\ServiceProvider;

final class ContentSeoSnapshotMaterializationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CanonicalPolicy::class);
        $this->app->singleton(ContentSeoSnapshotIdentityV1::class);
        $this->app->singleton(DeterministicContentSeoCanonicalPathPolicyV1::class);
        $this->app->alias(DeterministicContentSeoCanonicalPathPolicyV1::class, ContentSeoCanonicalPathPolicyV1::class);
        $this->app->singleton(PostgreSqlContentSeoMaterializationSourceReaderV1::class);
        $this->app->alias(PostgreSqlContentSeoMaterializationSourceReaderV1::class, ContentSeoMaterializationSourceReaderV1::class);
        $this->app->singleton(ContentSeoSourceSnapshotMapper::class);
        $this->app->singleton(PostgreSqlContentSeoSourceSnapshotReader::class);
        $this->app->alias(PostgreSqlContentSeoSourceSnapshotReader::class, ContentSeoSourceSnapshotReader::class);
        $this->app->singleton(PostgreSqlContentSeoSourceSnapshotWriter::class);
        $this->app->alias(PostgreSqlContentSeoSourceSnapshotWriter::class, ContentSeoSourceSnapshotWriter::class);
        $this->app->singleton(DeterministicContentSeoSnapshotMaterializerV1::class);
        $this->app->alias(DeterministicContentSeoSnapshotMaterializerV1::class, MaterializeContentSeoSnapshotV1::class);
        $this->app->singleton(DeterministicCatchUpContentSeoSnapshotV1::class);
        $this->app->alias(DeterministicCatchUpContentSeoSnapshotV1::class, CatchUpContentSeoSnapshotV1::class);
    }
}
