<?php

namespace App\Infrastructure\ProjectionRebuildRuntimeSource;

use App\Application\PublicProjectionRebuild\PublicProjectionRebuildScope;
use InvalidArgumentException;
use JsonException;

final readonly class RebuildCheckpointCodec
{
    public function encode(PublicProjectionRebuildScope $scope, string $after): string
    {
        $json = json_encode(['v' => 1, 'scope' => $this->fingerprint($scope), 'after' => $after], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        return rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
    }

    public function decode(PublicProjectionRebuildScope $scope, string $checkpoint): string
    {
        try {
            $padding = str_repeat('=', (4 - strlen($checkpoint) % 4) % 4);
            $json = base64_decode(strtr($checkpoint, '-_', '+/').$padding, true);
            if (! is_string($json)) {
                throw new InvalidArgumentException('Invalid rebuild checkpoint encoding.');
            }
            $payload = json_decode($json, true, 16, JSON_THROW_ON_ERROR);
            if (! is_array($payload)
                || ($payload['v'] ?? null) !== 1
                || ! hash_equals($this->fingerprint($scope), (string) ($payload['scope'] ?? ''))
                || trim((string) ($payload['after'] ?? '')) === '') {
                throw new InvalidArgumentException('Invalid or incompatible rebuild checkpoint.');
            }

            return (string) $payload['after'];
        } catch (JsonException $error) {
            throw new InvalidArgumentException('Invalid rebuild checkpoint payload.', 0, $error);
        }
    }

    private function fingerprint(PublicProjectionRebuildScope $scope): string
    {
        return hash('sha256', json_encode([
            'type' => $scope->type->value,
            'listingIds' => $scope->listingIds,
            'from' => $scope->from,
            'to' => $scope->to,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }
}
