<?php

namespace Tests\Unit\MediaIngestionRuntime;

use App\Application\MediaIngestionRuntime\DeterministicMediaIngestionRuntimeAvailabilityPolicy;
use App\Application\MediaIngestionRuntime\MediaIngestionRuntimeStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MediaIngestionRuntimeAvailabilityPolicyTest extends TestCase
{
    #[Test]
    public function it_is_ready_only_when_every_owner_binding_is_available(): void
    {
        $ready = new DeterministicMediaIngestionRuntimeAvailabilityPolicy([
            'media_upload' => true,
            'media_asset' => true,
            'media_processing' => true,
            'media_quota' => true,
        ]);
        self::assertSame(MediaIngestionRuntimeStatus::Ready, $ready->inspect()->status);
        self::assertNull($ready->inspect()->code);

        $missing = new DeterministicMediaIngestionRuntimeAvailabilityPolicy([
            'media_upload' => true,
            'media_asset' => false,
            'media_processing' => false,
            'media_quota' => true,
        ]);
        self::assertSame(MediaIngestionRuntimeStatus::MissingBinding, $missing->inspect()->status);
        self::assertSame('media_asset', $missing->inspect()->code);
    }

    #[Test]
    public function diagnostics_are_closed_and_contain_no_sensitive_context(): void
    {
        self::assertSame(
            ['Ready', 'MissingBinding', 'DependencyUnavailable', 'IncompatibleVersion'],
            array_column(MediaIngestionRuntimeStatus::cases(), 'value'),
        );
    }
}
