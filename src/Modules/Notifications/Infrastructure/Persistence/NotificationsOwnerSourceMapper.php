<?php

namespace Appart\Modules\Notifications\Infrastructure\Persistence;

use Appart\Modules\Notifications\Application\OwnerSource\NotificationChannelRevisionState;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationPreferenceRevisionState;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationTemplateRevisionState;
use Appart\Modules\Notifications\Application\PublicRead\NotificationChannelStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationTemplateStatusV1;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

/** @phpstan-type OwnerRow array{subject_key:string,stream_type:string,revision:int,decision:string,effective_at:string,recorded_at:string,revision_checksum:string} */
final readonly class NotificationsOwnerSourceMapper
{
    /** @return OwnerRow */
    public function preferenceToRow(NotificationPreferenceRevisionState $s): array
    {
        return $this->row($s->subjectKey, 'preference', $s->revision, $s->decision->value, $s->effectiveAt, $s->recordedAt);
    }

    /** @return OwnerRow */
    public function templateToRow(NotificationTemplateRevisionState $s): array
    {
        return $this->row($s->subjectKey, 'template', $s->revision, $s->decision->value, $s->effectiveAt, $s->recordedAt);
    }

    /** @return OwnerRow */
    public function channelToRow(NotificationChannelRevisionState $s): array
    {
        return $this->row($s->subjectKey, 'channel', $s->revision, $s->decision->value, $s->effectiveAt, $s->recordedAt);
    }

    /** @param OwnerRow $r */
    public function toPreferenceState(array $r): NotificationPreferenceRevisionState
    {
        $this->assertValid($r, 'preference');

        return new NotificationPreferenceRevisionState((string) $r['subject_key'], (int) $r['revision'], NotificationPreferenceStatusV1::from((string) $r['decision']), new DateTimeImmutable((string) $r['effective_at']), new DateTimeImmutable((string) $r['recorded_at']));
    }

    /** @param OwnerRow $r */
    public function toTemplateState(array $r): NotificationTemplateRevisionState
    {
        $this->assertValid($r, 'template');

        return new NotificationTemplateRevisionState((string) $r['subject_key'], (int) $r['revision'], NotificationTemplateStatusV1::from((string) $r['decision']), new DateTimeImmutable((string) $r['effective_at']), new DateTimeImmutable((string) $r['recorded_at']));
    }

    /** @param OwnerRow $r */
    public function toChannelState(array $r): NotificationChannelRevisionState
    {
        $this->assertValid($r, 'channel');

        return new NotificationChannelRevisionState((string) $r['subject_key'], (int) $r['revision'], NotificationChannelStatusV1::from((string) $r['decision']), new DateTimeImmutable((string) $r['effective_at']), new DateTimeImmutable((string) $r['recorded_at']));
    }

    /** @return OwnerRow */
    private function row(string $key, string $stream, int $revision, string $decision, DateTimeImmutable $effective, DateTimeImmutable $recorded): array
    {
        $r = ['subject_key' => $key, 'stream_type' => $stream, 'revision' => $revision, 'decision' => $decision, 'effective_at' => $this->canonical($effective), 'recorded_at' => $this->canonical($recorded)];

        return $r + ['revision_checksum' => $this->checksum($r)];
    }

    /** @param OwnerRow $r */
    private function assertValid(array $r, string $stream): void
    {
        try {
            if ((string) $r['stream_type'] !== $stream || ! hash_equals((string) $r['revision_checksum'], $this->checksum($r))) {
                throw new RuntimeException('Notifications owner source checksum mismatch.');
            }
        } catch (Throwable $e) {
            throw new RuntimeException('Invalid Notifications owner source row.', 0, $e);
        }
    }

    /** @param array{subject_key:string,stream_type:string,revision:int,decision:string,effective_at:string,recorded_at:string} $r */
    private function checksum(array $r): string
    {
        return hash('sha256', implode("\n", [(string) $r['subject_key'], (string) $r['stream_type'], (string) $r['revision'], (string) $r['decision'], $this->canonical(new DateTimeImmutable((string) $r['effective_at'])), $this->canonical(new DateTimeImmutable((string) $r['recorded_at']))]));
    }

    private function canonical(DateTimeImmutable $i): string
    {
        return $i->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}
