<?php

namespace Tests\Unit\Notifications\Http;

use App\Http\Notifications\NotificationsResponseFactory;
use Appart\Modules\Notifications\Application\PublicRead\NotificationChannelResultV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationChannelStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceResultV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationPreferenceStatusV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationTemplateResultV1;
use Appart\Modules\Notifications\Application\PublicRead\NotificationTemplateStatusV1;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NotificationsResponseFactoryTest extends TestCase
{
    #[DataProvider('mappings')]
    public function test_mapping_is_exhaustive(string $kind, object $result, int $expected): void
    {
        $response = (new NotificationsResponseFactory)->{$kind}($result);
        self::assertSame($expected, $response->getStatusCode());
        self::assertSame(['status' => $result->status->value], $response->getData(true));
    }

    /** @return iterable<string, array{string, NotificationPreferenceResultV1|NotificationTemplateResultV1|NotificationChannelResultV1, int}> */
    public static function mappings(): iterable
    {
        foreach ([[NotificationPreferenceStatusV1::Enabled, 200], [NotificationPreferenceStatusV1::Disabled, 200], [NotificationPreferenceStatusV1::Missing, 404], [NotificationPreferenceStatusV1::Corrupted, 503], [NotificationPreferenceStatusV1::DependencyUnavailable, 503]] as [$status,$http]) {
            yield 'preference '.$status->value => ['preference', new NotificationPreferenceResultV1($status), $http];
        }
        foreach ([[NotificationTemplateStatusV1::Available, 200], [NotificationTemplateStatusV1::Missing, 404], [NotificationTemplateStatusV1::Corrupted, 503], [NotificationTemplateStatusV1::DependencyUnavailable, 503]] as [$status,$http]) {
            yield 'template '.$status->value => ['template', new NotificationTemplateResultV1($status), $http];
        }
        foreach ([[NotificationChannelStatusV1::Allowed, 200], [NotificationChannelStatusV1::Blocked, 200], [NotificationChannelStatusV1::Missing, 404], [NotificationChannelStatusV1::Corrupted, 503], [NotificationChannelStatusV1::DependencyUnavailable, 503]] as [$status,$http]) {
            yield 'channel '.$status->value => ['channel', new NotificationChannelResultV1($status), $http];
        }
    }
}
