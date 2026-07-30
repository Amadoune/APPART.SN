<?php

namespace Appart\Modules\ModerationReports\Infrastructure\Persistence\QueueOwnerReadSource;

use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadItemV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadStateV1;
use DateTimeImmutable;
use UnexpectedValueException;

final class ModerationQueueReadMapperV1
{
    /** @param array<string, mixed> $row */
    public function item(array $row): ModerationQueueReadItemV1
    {
        $state = ModerationQueueReadStateV1::tryFrom((string) ($row['state'] ?? ''));
        if ($state === null || ! self::uuid($row['queue_item_id'] ?? null) || ! self::uuid($row['case_id'] ?? null)) {
            throw new UnexpectedValueException('Corrupted moderation queue item.');
        }
        $leaseExpiresAt = $row['lease_expires_at'] === null
            ? null
            : new DateTimeImmutable((string) $row['lease_expires_at']);
        if (($state === ModerationQueueReadStateV1::Claimed) !== ($leaseExpiresAt !== null)) {
            throw new UnexpectedValueException('Corrupted moderation queue lease.');
        }

        return new ModerationQueueReadItemV1(
            (string) $row['queue_item_id'],
            (string) $row['case_id'],
            (int) $row['priority'],
            (string) $row['category'],
            $state,
            $leaseExpiresAt,
            (int) $row['source_version'],
            new DateTimeImmutable((string) $row['updated_at']),
        );
    }

    private static function uuid(mixed $value): bool
    {
        return is_string($value)
            && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
    }
}
