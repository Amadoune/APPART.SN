<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ExperienceAcceptanceTransportFoundationArchitectureTest extends TestCase
{
    public function test_transport_depends_exclusively_on_certified_outbox_messages(): void
    {
        $root = dirname(__DIR__, 2);
        $files = glob($root.'/src/Modules/ExperienceAcceptance/Application/Transport/*.php') ?: [];
        self::assertCount(4, $files);
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));
        self::assertStringContainsString('Application\\Outbox\\ExperienceAcceptanceOutboxMessage', $php);
        foreach (['Application\\Delivery', 'Application\\Event', 'PublicRead', 'OwnerSource', 'Application\\Runtime', 'App\\Http', 'PostgreSql', 'Infrastructure\\', 'Routing', 'Consumer', 'PDO', 'Repository', 'Mapper'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_transport_fields_are_closed_and_migrations_are_frozen(): void
    {
        $root = dirname(__DIR__, 2);
        $envelope = (string) file_get_contents($root.'/src/Modules/ExperienceAcceptance/Application/Transport/ExperienceAcceptanceTransportEnvelope.php');
        foreach (['messageId', 'eventId', 'type', 'status', 'observedAt', 'checksum'] as $field) {
            self::assertStringContainsString('$'.$field, $envelope);
        }
        $persistence = $root.'/src/Modules/ExperienceAcceptance/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('0c49f30b31338537045a2e603c7354844e871efd45c18a32b3b2fb11e7776059', hash_file('sha256', $persistence.'090_experience_acceptance_owner_source.sql'));
        self::assertSame('3fd63032924715010ec6a703a81ebf8ed31c38f9ce8730a7a1937af03600d79a', hash_file('sha256', $persistence.'090_experience_acceptance_owner_source.down.sql'));
        $outbox = $root.'/src/Modules/ExperienceAcceptance/Infrastructure/Outbox/Migrations/';
        self::assertSame('e9020dba7909f7d2459a4aa018c2aef84608113c85815f0c14ee548e8aa1c5db', hash_file('sha256', $outbox.'091_experience_acceptance_outbox.sql'));
        self::assertSame('660357d94cf7f10cb3480a5f226cd37cc09d6dcd7f5e1588af65de248c6fdce2', hash_file('sha256', $outbox.'091_experience_acceptance_outbox.down.sql'));
    }
}
