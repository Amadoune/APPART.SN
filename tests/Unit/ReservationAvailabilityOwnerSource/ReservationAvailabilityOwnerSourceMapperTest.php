<?php

namespace Tests\Unit\ReservationAvailabilityOwnerSource;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityRevisionDecision;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityRevisionState;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityIntentId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilitySubjectId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityWindow;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\ReservationAvailabilityOwnerSourceMapper;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ReservationAvailabilityOwnerSourceMapperTest extends TestCase
{
    #[Test]
    public function states_and_checksum_round_trip_canonically(): void
    {
        $mapper = new ReservationAvailabilityOwnerSourceMapper;
        $state = self::state(1, ReservationAvailabilityRevisionDecision::Proposed, '08:00');
        $row = $mapper->toRow($state);

        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $row['checksum']);
        self::assertSame($row['checksum'], $mapper->checksum($row));

        $restored = $mapper->toState([
            'availability_intent_id' => $row['intent_id'],
            'revision' => $row['revision'],
            'availability_subject_id' => $row['subject_id'],
            'window_start' => $row['window_start'],
            'window_end' => $row['window_end'],
            'decision' => $row['decision'],
            'effective_at' => $row['effective_at'],
            'recorded_at' => $row['recorded_at'],
            'revision_checksum' => $row['checksum'],
        ]);

        self::assertSame($state->intentId->value, $restored->intentId->value);
        self::assertSame($state->subjectId->value, $restored->subjectId->value);
        self::assertSame($state->window->canonical(), $restored->window->canonical());
        self::assertSame($state->decision, $restored->decision);
    }

    #[Test]
    public function mapper_rejects_a_divergent_checksum(): void
    {
        $mapper = new ReservationAvailabilityOwnerSourceMapper;
        $row = $mapper->toRow(self::state(1, ReservationAvailabilityRevisionDecision::Proposed, '08:00'));
        $row['availability_intent_id'] = $row['intent_id'];
        $row['availability_subject_id'] = $row['subject_id'];
        $row['revision_checksum'] = str_repeat('0', 64);

        $this->expectException(RuntimeException::class);
        $mapper->toState($row);
    }

    #[Test]
    public function decisions_define_only_the_five_certified_internal_states(): void
    {
        self::assertSame(
            ['proposed', 'held', 'committed', 'released', 'expired'],
            array_column(ReservationAvailabilityRevisionDecision::cases(), 'value'),
        );
        self::assertFalse(ReservationAvailabilityRevisionDecision::Proposed->blocksAvailability());
        self::assertTrue(ReservationAvailabilityRevisionDecision::Held->blocksAvailability());
        self::assertTrue(ReservationAvailabilityRevisionDecision::Committed->blocksAvailability());
        self::assertFalse(ReservationAvailabilityRevisionDecision::Released->blocksAvailability());
        self::assertFalse(ReservationAvailabilityRevisionDecision::Expired->blocksAvailability());
    }

    public static function intent(int $suffix = 1): ReservationAvailabilityIntentId
    {
        return ReservationAvailabilityIntentId::fromString(sprintf('01922f8e-7c44-7abc-8def-%012d', $suffix));
    }

    public static function subject(): ReservationAvailabilitySubjectId
    {
        return ReservationAvailabilitySubjectId::fromString('01922f8e-7c44-7abc-8def-900000000001');
    }

    public static function window(): ReservationAvailabilityWindow
    {
        return new ReservationAvailabilityWindow(
            new DateTimeImmutable('2026-08-10T12:00:00Z'),
            new DateTimeImmutable('2026-08-12T08:00:00Z'),
        );
    }

    public static function state(
        int $revision,
        ReservationAvailabilityRevisionDecision $decision,
        string $time,
        int $intentSuffix = 1,
    ): ReservationAvailabilityRevisionState {
        $effectiveAt = new DateTimeImmutable('2026-08-01T'.$time.':00Z');

        return new ReservationAvailabilityRevisionState(
            self::intent($intentSuffix),
            $revision,
            self::subject(),
            self::window(),
            $decision,
            $effectiveAt,
            $effectiveAt->modify('+1 minute'),
        );
    }
}
