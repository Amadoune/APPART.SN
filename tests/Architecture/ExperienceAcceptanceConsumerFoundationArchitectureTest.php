<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ExperienceAcceptanceConsumerFoundationArchitectureTest extends TestCase
{
    public function test_consumer_depends_exclusively_on_certified_routing(): void
    {
        $root = dirname(__DIR__, 2);
        $files = glob($root.'/src/Modules/ExperienceAcceptance/Application/Consumer/*.php') ?: [];
        self::assertCount(5, $files);
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));
        self::assertStringContainsString('Application\\Routing\\ExperienceAcceptanceRoutingResult', $php);
        foreach (['Application\\Transport', 'Application\\Outbox', 'Application\\Delivery', 'Application\\Event', 'PublicRead', 'OwnerSource', 'Application\\Runtime', 'App\\Http', 'Infrastructure\\', 'PDO', 'PostgreSql', 'Repository', 'Mapper', 'Illuminate\\'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_consumer_has_no_effect_surface_and_migrations_are_frozen(): void
    {
        $root = dirname(__DIR__, 2);
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), glob($root.'/src/Modules/ExperienceAcceptance/Application/Consumer/*.php') ?: []));
        foreach (['save(', 'write(', 'append(', 'dispatch(', 'publish(', 'send('] as $effect) {
            self::assertStringNotContainsString($effect, $php);
        }
        $persistence = $root.'/src/Modules/ExperienceAcceptance/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('0c49f30b31338537045a2e603c7354844e871efd45c18a32b3b2fb11e7776059', hash_file('sha256', $persistence.'090_experience_acceptance_owner_source.sql'));
        self::assertSame('3fd63032924715010ec6a703a81ebf8ed31c38f9ce8730a7a1937af03600d79a', hash_file('sha256', $persistence.'090_experience_acceptance_owner_source.down.sql'));
        $outbox = $root.'/src/Modules/ExperienceAcceptance/Infrastructure/Outbox/Migrations/';
        self::assertSame('e9020dba7909f7d2459a4aa018c2aef84608113c85815f0c14ee548e8aa1c5db', hash_file('sha256', $outbox.'091_experience_acceptance_outbox.sql'));
        self::assertSame('660357d94cf7f10cb3480a5f226cd37cc09d6dcd7f5e1588af65de248c6fdce2', hash_file('sha256', $outbox.'091_experience_acceptance_outbox.down.sql'));
    }
}
