<?php

namespace Tests\Architecture;

use Appart\Modules\ExperienceAcceptance\Application\Routing\ExperienceAcceptanceRouteDestination;
use PHPUnit\Framework\TestCase;

final class ExperienceAcceptanceRoutingFoundationArchitectureTest extends TestCase
{
    public function test_routing_depends_exclusively_on_transport(): void
    {
        $root = dirname(__DIR__, 2);
        $files = glob($root.'/src/Modules/ExperienceAcceptance/Application/Routing/*.php') ?: [];
        self::assertCount(5, $files);
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));
        self::assertStringContainsString('Application\\Transport\\ExperienceAcceptanceTransportEnvelope', $php);
        foreach (['Application\\Outbox', 'Application\\Delivery', 'Application\\Event', 'PublicRead', 'OwnerSource', 'Application\\Runtime', 'App\\Http', 'Infrastructure\\', 'Consumer', 'PDO', 'PostgreSql', 'Repository', 'Mapper'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_destination_catalogue_is_closed_and_migrations_are_frozen(): void
    {
        self::assertSame(7, count(ExperienceAcceptanceRouteDestination::cases()));
        $root = dirname(__DIR__, 2);
        $persistence = $root.'/src/Modules/ExperienceAcceptance/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('0c49f30b31338537045a2e603c7354844e871efd45c18a32b3b2fb11e7776059', hash_file('sha256', $persistence.'090_experience_acceptance_owner_source.sql'));
        self::assertSame('3fd63032924715010ec6a703a81ebf8ed31c38f9ce8730a7a1937af03600d79a', hash_file('sha256', $persistence.'090_experience_acceptance_owner_source.down.sql'));
        $outbox = $root.'/src/Modules/ExperienceAcceptance/Infrastructure/Outbox/Migrations/';
        self::assertSame('e9020dba7909f7d2459a4aa018c2aef84608113c85815f0c14ee548e8aa1c5db', hash_file('sha256', $outbox.'091_experience_acceptance_outbox.sql'));
        self::assertSame('660357d94cf7f10cb3480a5f226cd37cc09d6dcd7f5e1588af65de248c6fdce2', hash_file('sha256', $outbox.'091_experience_acceptance_outbox.down.sql'));
    }
}
