<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class TransactionalRuntimeCompositionArchitectureTest extends TestCase
{
    public function test_provider_only_composes_the_three_certified_transaction_participants(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertIsString($contents);
        self::assertSame(5, substr_count($contents, 'PostgreSqlAggregateOutboxParticipantTransaction'));
        foreach (['PostgreSqlListingRepository', 'PostgreSqlPropertyRepository', 'PostgreSqlMediaCollectionRepository'] as $repository) {
            self::assertStringContainsString("new {$repository}(", $contents);
        }
        foreach (['PostgreSqlSearchDecisionReader', 'PostgreSqlContentSeoSourceSnapshotReader'] as $reader) {
            self::assertStringNotContainsString("new {$reader}(", $contents);
        }
    }
}
