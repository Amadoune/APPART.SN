<?php

namespace Tests\Unit\ContactsLeads\AntiAbuseOwnerSource;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionDecision;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionState;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\AntiAbuseOwnerSourceMapper;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class AntiAbuseOwnerSourceMapperTest extends TestCase
{
    public function test_state_normalizes_instants_and_mapper_is_bijective(): void
    {
        $state = self::state();
        $mapper = new AntiAbuseOwnerSourceMapper;
        $row = $mapper->toRow($state);

        self::assertSame('2026-07-31T08:00:00.123456Z', $row['effective_at']);
        self::assertSame('2026-07-31T08:01:00.123456Z', $row['recorded_at']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $row['checksum']);

        $restored = $mapper->toState([
            'lead_ingress_intent_id' => $row['intent_id'],
            'revision' => $row['revision'],
            'decision' => $row['decision'],
            'effective_at' => $row['effective_at'],
            'recorded_at' => $row['recorded_at'],
            'policy_reference' => $row['policy_reference'],
            'revision_checksum' => $row['checksum'],
        ]);
        self::assertEquals($state, $restored);
        self::assertSame($row['checksum'], $mapper->toRow($restored)['checksum']);
    }

    public function test_mapper_rejects_checksum_divergence(): void
    {
        $row = (new AntiAbuseOwnerSourceMapper)->toRow(self::state());
        $this->expectException(RuntimeException::class);
        (new AntiAbuseOwnerSourceMapper)->toState([
            'lead_ingress_intent_id' => $row['intent_id'],
            'revision' => $row['revision'],
            'decision' => $row['decision'],
            'effective_at' => $row['effective_at'],
            'recorded_at' => $row['recorded_at'],
            'policy_reference' => $row['policy_reference'],
            'revision_checksum' => str_repeat('0', 64),
        ]);
    }

    #[DataProvider('invalidStateCases')]
    public function test_state_rejects_invalid_shapes(int $revision, string $recordedAt, ?string $policyReference): void
    {
        $this->expectException(InvalidArgumentException::class);
        new AntiAbuseRevisionState(
            self::intent(),
            $revision,
            AntiAbuseRevisionDecision::Allowed,
            new DateTimeImmutable('2026-07-31T08:00:00Z'),
            new DateTimeImmutable($recordedAt),
            $policyReference,
        );
    }

    /** @return iterable<string, array{int,string,?string}> */
    public static function invalidStateCases(): iterable
    {
        yield 'non-positive revision' => [0, '2026-07-31T08:01:00Z', null];
        yield 'recorded before effective' => [1, '2026-07-31T07:59:00Z', null];
        yield 'invalid policy reference' => [1, '2026-07-31T08:01:00Z', 'PII is forbidden'];
    }

    public static function intent(): LeadIngressIntentId
    {
        return LeadIngressIntentId::fromString('00000000-0000-4000-8000-000000005407');
    }

    public static function state(): AntiAbuseRevisionState
    {
        return new AntiAbuseRevisionState(
            self::intent(),
            1,
            AntiAbuseRevisionDecision::Allowed,
            new DateTimeImmutable('2026-07-31T10:00:00.123456+02:00'),
            new DateTimeImmutable('2026-07-31T10:01:00.123456+02:00'),
            'anti-abuse-v1',
        );
    }
}
