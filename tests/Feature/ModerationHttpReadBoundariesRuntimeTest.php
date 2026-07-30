<?php

namespace Tests\Feature;

use App\Application\ModerationHttpReadBoundaries\OwnerReadModerationCaseV1;
use App\Application\ModerationHttpReadBoundaries\OwnerReadModerationDecisionV1;
use App\Application\ModerationHttpReadBoundaries\OwnerReadModerationQueueV1;
use App\Application\ModerationHttpReadBoundaries\OwnerReadOwnModerationReportV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\Contract\ModeratorAuthorizationReaderV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadModerationCaseV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadModerationDecisionV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadModerationQueueV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadOwnModerationReportV1;
use PDO;
use Tests\TestCase;

final class ModerationHttpReadBoundariesRuntimeTest extends TestCase
{
    public function test_container_resolves_all_four_read_boundaries_as_unique_singletons(): void
    {
        $this->app->instance(PDO::class, $this->createStub(PDO::class));
        $this->app->instance(
            ModeratorAuthorizationReaderV1::class,
            $this->createStub(ModeratorAuthorizationReaderV1::class),
        );

        $bindings = [
            ReadOwnModerationReportV1::class => OwnerReadOwnModerationReportV1::class,
            ReadModerationQueueV1::class => OwnerReadModerationQueueV1::class,
            ReadModerationCaseV1::class => OwnerReadModerationCaseV1::class,
            ReadModerationDecisionV1::class => OwnerReadModerationDecisionV1::class,
        ];

        foreach ($bindings as $contract => $implementation) {
            $first = $this->app->make($contract);
            self::assertInstanceOf($implementation, $first);
            self::assertSame($first, $this->app->make($contract));
        }
    }
}
