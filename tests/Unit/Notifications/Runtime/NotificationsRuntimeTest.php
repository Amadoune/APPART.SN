<?php

namespace Tests\Unit\Notifications\Runtime;

use Appart\Modules\Notifications\Application\OwnerSource\NotificationChannelReadResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationPreferenceReadResult;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationsOwnerSource;
use Appart\Modules\Notifications\Application\OwnerSource\NotificationTemplateReadResult;
use Appart\Modules\Notifications\Application\Runtime\DeterministicNotificationsRuntime;
use Appart\Modules\Notifications\Application\Runtime\DeterministicNotificationsRuntimeAvailabilityPolicy;
use Appart\Modules\Notifications\Application\Runtime\NotificationsRuntimeAvailability;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class NotificationsRuntimeTest extends TestCase
{
    #[DataProvider('availabilityCases')]
    public function test_policy_reduces_owner_source_results_mechanically(NotificationPreferenceReadResult $preference, NotificationTemplateReadResult $template, NotificationChannelReadResult $channel, NotificationsRuntimeAvailability $expected): void
    {
        $source = $this->createMock(NotificationsOwnerSource::class);
        $source->method('readPreference')->willReturn($preference);
        $source->method('readTemplate')->willReturn($template);
        $source->method('readChannel')->willReturn($channel);

        self::assertSame($expected, (new DeterministicNotificationsRuntimeAvailabilityPolicy($source))->inspect());
    }

    /** @return iterable<string, array{NotificationPreferenceReadResult, NotificationTemplateReadResult, NotificationChannelReadResult, NotificationsRuntimeAvailability}> */
    public static function availabilityCases(): iterable
    {
        yield 'missing is technically available' => [NotificationPreferenceReadResult::missing(), NotificationTemplateReadResult::missing(), NotificationChannelReadResult::missing(), NotificationsRuntimeAvailability::Available];
        yield 'corrupted is fail closed' => [NotificationPreferenceReadResult::corrupted(), NotificationTemplateReadResult::missing(), NotificationChannelReadResult::missing(), NotificationsRuntimeAvailability::Corrupted];
        yield 'dependency failure has priority' => [NotificationPreferenceReadResult::corrupted(), NotificationTemplateReadResult::dependencyUnavailable(), NotificationChannelReadResult::missing(), NotificationsRuntimeAvailability::DependencyUnavailable];
    }

    public function test_exception_is_dependency_unavailable_and_diagnostics_are_minimal(): void
    {
        $source = $this->createMock(NotificationsOwnerSource::class);
        $source->method('readPreference')->willThrowException(new RuntimeException('technical'));
        $runtime = new DeterministicNotificationsRuntime(new DeterministicNotificationsRuntimeAvailabilityPolicy($source));

        self::assertSame(NotificationsRuntimeAvailability::DependencyUnavailable, $runtime->availability());
        self::assertSame(['runtimeId' => 'notifications.owner-source', 'version' => 'notifications-runtime-v1', 'availability' => NotificationsRuntimeAvailability::DependencyUnavailable], get_object_vars($runtime->diagnostics()));
    }
}
