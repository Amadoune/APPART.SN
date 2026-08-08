<?php

namespace Tests\Unit\ContactsLeads\ConsentOwnerSource;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionDecision;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSource\ConsentRevisionState;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\ConsentOwnerSourceMapper;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

final class ConsentOwnerSourceMapperTest extends TestCase
{
    #[Test]
    public function state_is_immutable_minimal_and_normalized_to_utc(): void
    {
        $state = self::state();

        self::assertSame('2026-07-31T08:00:00.000000+00:00', $state->effectiveAt->format('Y-m-d\TH:i:s.uP'));
        self::assertSame('2026-07-31T08:01:00.000000+00:00', $state->recordedAt->format('Y-m-d\TH:i:s.uP'));
        self::assertSame('contact-v1', $state->policyReference);
        $properties = array_map(
            static fn (\ReflectionProperty $property): string => $property->getName(),
            (new ReflectionClass($state))->getProperties(),
        );
        foreach (['accountId', 'listingId', 'professionalId', 'content'] as $forbidden) {
            self::assertNotContains($forbidden, $properties);
        }
    }

    #[Test]
    public function mapper_is_bijective_and_checksum_is_canonical(): void
    {
        $mapper = new ConsentOwnerSourceMapper;
        $row = $mapper->toRow(self::state());
        $stored = [
            'lead_ingress_intent_id' => $row['intent_id'],
            'revision' => $row['revision'],
            'decision' => $row['decision'],
            'effective_at' => $row['effective_at'],
            'recorded_at' => $row['recorded_at'],
            'policy_reference' => $row['policy_reference'],
            'revision_checksum' => $row['checksum'],
        ];

        self::assertSame(64, strlen($row['checksum']));
        self::assertSame($row['checksum'], $mapper->toRow($mapper->toState($stored))['checksum']);
        self::assertSame(ConsentRevisionDecision::Granted, $mapper->toState($stored)->decision);

        $stored['decision'] = 'denied';
        $this->expectException(RuntimeException::class);
        $mapper->toState($stored);
    }

    #[Test]
    public function state_rejects_invalid_revision_chronology_and_policy_reference(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ConsentRevisionState(
            self::intent(),
            0,
            ConsentRevisionDecision::Granted,
            new DateTimeImmutable('2026-07-31T08:00:00Z'),
            new DateTimeImmutable('2026-07-31T07:59:59Z'),
            'CONTACT TEXT',
        );
    }

    public static function state(
        int $revision = 1,
        ConsentRevisionDecision $decision = ConsentRevisionDecision::Granted,
        string $effectiveAt = '2026-07-31T10:00:00+02:00',
        string $recordedAt = '2026-07-31T10:01:00+02:00',
    ): ConsentRevisionState {
        return new ConsentRevisionState(
            self::intent(),
            $revision,
            $decision,
            new DateTimeImmutable($effectiveAt),
            new DateTimeImmutable($recordedAt),
            'contact-v1',
        );
    }

    public static function intent(): LeadIngressIntentId
    {
        return LeadIngressIntentId::fromString('019428b8-5d5d-7c28-8a8f-8796c8732f91');
    }
}
