<?php

namespace Appart\Modules\ModerationReports\Infrastructure\Persistence\QueueOwnerReadSource;

use DateTimeImmutable;
use Throwable;

final readonly class ModerationQueueCursorCodecV1
{
    public function __construct(private string $integrityKey) {}

    public function encode(ModerationQueueCursorPositionV1 $position, string $filterChecksum): string
    {
        $payload = json_encode([
            'filter' => $filterChecksum,
            'priority' => $position->priority,
            'queueItemId' => $position->queueItemId,
            'updatedAt' => $position->updatedAt->format('Y-m-d\TH:i:s.uP'),
            'version' => 1,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        return self::base64url($payload).'.'.hash_hmac('sha256', $payload, $this->integrityKey);
    }

    public function decode(string $cursor, string $filterChecksum): ?ModerationQueueCursorPositionV1
    {
        try {
            [$encoded, $signature] = array_pad(explode('.', $cursor, 2), 2, null);
            if (! is_string($encoded) || ! is_string($signature)) {
                return null;
            }
            $payload = self::decodeBase64url($encoded);
            if ($payload === null || ! hash_equals(hash_hmac('sha256', $payload, $this->integrityKey), $signature)) {
                return null;
            }
            $data = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($data)
                || $data['version'] !== 1
                || $data['filter'] !== $filterChecksum
                || ! is_int($data['priority'])
                || ! is_string($data['updatedAt'])
                || ! is_string($data['queueItemId'])
            ) {
                return null;
            }

            return new ModerationQueueCursorPositionV1(
                $data['priority'],
                new DateTimeImmutable($data['updatedAt']),
                $data['queueItemId'],
            );
        } catch (Throwable) {
            return null;
        }
    }

    private static function base64url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function decodeBase64url(string $value): ?string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return is_string($decoded) ? $decoded : null;
    }
}
