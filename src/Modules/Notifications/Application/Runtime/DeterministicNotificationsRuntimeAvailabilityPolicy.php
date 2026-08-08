<?php

namespace Appart\Modules\Notifications\Application\Runtime;

use Appart\Modules\Notifications\Application\OwnerSource\NotificationsOwnerSource;
use Appart\Modules\Notifications\Application\PublicRead\NotificationChannelStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationObservedAt;
use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationSubjectKey;
use Appart\Modules\Notifications\Application\PublicRead\NotificationTemplateStatusV1;
use DateTimeImmutable;
use Throwable;

final readonly class DeterministicNotificationsRuntimeAvailabilityPolicy implements NotificationsRuntimeAvailabilityPolicy
{
    private const PROBE_SUBJECT = 'runtime/notifications-owner-source';

    public function __construct(private NotificationsOwnerSource $source) {}

    public function inspect(): NotificationsRuntimeAvailability
    {
        try {
            $subject = new NotificationSubjectKey(self::PROBE_SUBJECT);
            $observedAt = new NotificationObservedAt(new DateTimeImmutable('9999-12-31T23:59:59.999999Z'));
            $preference = $this->source->readPreference($subject, $observedAt);
            $template = $this->source->readTemplate($subject, $observedAt);
            $channel = $this->source->readChannel($subject, $observedAt);

            if ($preference->status === NotificationPreferenceStatusV1::DependencyUnavailable
                || $template->status === NotificationTemplateStatusV1::DependencyUnavailable
                || $channel->status === NotificationChannelStatusV1::DependencyUnavailable) {
                return NotificationsRuntimeAvailability::DependencyUnavailable;
            }
            if ($preference->status === NotificationPreferenceStatusV1::Corrupted
                || $template->status === NotificationTemplateStatusV1::Corrupted
                || $channel->status === NotificationChannelStatusV1::Corrupted) {
                return NotificationsRuntimeAvailability::Corrupted;
            }

            return NotificationsRuntimeAvailability::Available;
        } catch (Throwable) {
            return NotificationsRuntimeAvailability::DependencyUnavailable;
        }
    }
}
