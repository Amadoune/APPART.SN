<?php

namespace Tests\Feature;

use App\Infrastructure\ContentSeoSnapshotMaterialization\PostgreSqlContentSeoMaterializationSourceReaderV1;
use Appart\Modules\ContentSeo\Application\Materialization\Contract\CatchUpContentSeoSnapshotV1;
use Appart\Modules\ContentSeo\Application\Materialization\Contract\ContentSeoMaterializationSourceReaderV1;
use Appart\Modules\ContentSeo\Application\Materialization\Contract\MaterializeContentSeoSnapshotV1;
use Appart\Modules\ContentSeo\Application\Materialization\DeterministicCatchUpContentSeoSnapshotV1;
use Appart\Modules\ContentSeo\Application\Materialization\DeterministicContentSeoSnapshotMaterializerV1;
use Tests\TestCase;

final class ContentSeoSnapshotMaterializationBindingTest extends TestCase
{
    public function test_productive_materialization_composition_is_bound_as_singletons(): void
    {
        self::assertInstanceOf(PostgreSqlContentSeoMaterializationSourceReaderV1::class, $this->app->make(ContentSeoMaterializationSourceReaderV1::class));
        self::assertInstanceOf(DeterministicContentSeoSnapshotMaterializerV1::class, $this->app->make(MaterializeContentSeoSnapshotV1::class));
        self::assertInstanceOf(DeterministicCatchUpContentSeoSnapshotV1::class, $this->app->make(CatchUpContentSeoSnapshotV1::class));
        self::assertSame($this->app->make(MaterializeContentSeoSnapshotV1::class), $this->app->make(MaterializeContentSeoSnapshotV1::class));
    }
}
