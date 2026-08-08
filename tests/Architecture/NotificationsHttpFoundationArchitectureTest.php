<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class NotificationsHttpFoundationArchitectureTest extends TestCase
{
    public function test_controllers_depend_only_on_public_v1_readers(): void
    {
        $root = dirname(__DIR__, 2);
        $files = array_merge(glob($root.'/app/Http/Controllers/Notification*Controller.php'), glob($root.'/app/Http/Requests/Notification*Request.php'), glob($root.'/app/Http/Notifications/*.php'));
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));
        foreach (['NotificationPreferenceReaderV1', 'NotificationTemplateReaderV1', 'NotificationChannelReaderV1'] as $reader) {
            self::assertStringContainsString($reader, $php);
        }
        foreach (['NotificationsOwnerSource', 'Application\\Runtime', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'Event\\', 'Delivery\\', 'Outbox\\', 'Consumer', 'Transport', 'Routing', 'SQL'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_provider_registers_only_http_components(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/NotificationsHttpServiceProvider.php');
        foreach (['NotificationsResponseFactory', 'NotificationPreferenceController', 'NotificationTemplateController', 'NotificationChannelController'] as $binding) {
            self::assertSame(1, substr_count($provider, 'singleton('.$binding.'::class'));
        }
        foreach (['NotificationsOwnerSource', 'PostgreSql', 'Mapper', 'RuntimeServiceProvider', 'Event', 'Delivery', 'Outbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }
}
