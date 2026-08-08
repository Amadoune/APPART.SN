<?php

namespace Tests\Unit\Notifications\OwnerSource;

use Appart\Modules\Notifications\Application\OwnerSource\NotificationPreferenceRevisionState;
use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceStatusV1;
use Appart\Modules\Notifications\Infrastructure\Persistence\NotificationsOwnerSourceMapper;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NotificationsOwnerSourceMapperTest extends TestCase
{
    #[Test]
    public function it_maps_canonically_and_rejects_a_divergent_checksum(): void
    {
        $mapper = new NotificationsOwnerSourceMapper;
        $state = new NotificationPreferenceRevisionState('subject:42', 1, NotificationPreferenceStatusV1::Enabled, new DateTimeImmutable('2026-08-02T10:00:00.123456+02:00'), new DateTimeImmutable('2026-08-02T10:00:01.123456+02:00'));
        $row = $mapper->preferenceToRow($state);

        self::assertSame('2026-08-02T08:00:00.123456Z', $row['effective_at']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $row['revision_checksum']);
        self::assertSame(NotificationPreferenceStatusV1::Enabled, $mapper->toPreferenceState($row)->decision);

        $row['decision'] = 'disabled';
        $this->expectException(\RuntimeException::class);
        $mapper->toPreferenceState($row);
    }
}
