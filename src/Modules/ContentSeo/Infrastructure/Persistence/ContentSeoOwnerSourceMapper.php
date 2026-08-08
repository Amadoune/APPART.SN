<?php

namespace Appart\Modules\ContentSeo\Infrastructure\Persistence;

use Appart\Modules\ContentSeo\Application\OwnerSource\EditorialContentRevisionState;
use Appart\Modules\ContentSeo\Application\OwnerSource\OperationalSeoRevisionState;
use Appart\Modules\ContentSeo\Application\PublicRead\EditorialContentStatusV1;
use Appart\Modules\ContentSeo\Application\PublicRead\OperationalSeoStatusV1;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Throwable;

final readonly class ContentSeoOwnerSourceMapper
{
    /** @return array{resource_key:string,stream_type:string,revision:int,decision:string,effective_at:string,recorded_at:string,revision_checksum:string} */
    public function editorialToRow(EditorialContentRevisionState $state): array
    {
        return $this->row($state->resourceKey, 'editorial', $state->revision, $state->decision->value, $state->effectiveAt, $state->recordedAt);
    }

    /** @return array{resource_key:string,stream_type:string,revision:int,decision:string,effective_at:string,recorded_at:string,revision_checksum:string} */
    public function operationalSeoToRow(OperationalSeoRevisionState $state): array
    {
        return $this->row($state->resourceKey, 'operational_seo', $state->revision, $state->decision->value, $state->effectiveAt, $state->recordedAt);
    }

    /** @param array<string, mixed> $row */
    public function toEditorialState(array $row): EditorialContentRevisionState
    {
        $this->assertValid($row, 'editorial');

        return new EditorialContentRevisionState((string) $row['resource_key'], (int) $row['revision'], EditorialContentStatusV1::from((string) $row['decision']), new DateTimeImmutable((string) $row['effective_at']), new DateTimeImmutable((string) $row['recorded_at']));
    }

    /** @param array<string, mixed> $row */
    public function toOperationalSeoState(array $row): OperationalSeoRevisionState
    {
        $this->assertValid($row, 'operational_seo');

        return new OperationalSeoRevisionState((string) $row['resource_key'], (int) $row['revision'], OperationalSeoStatusV1::from((string) $row['decision']), new DateTimeImmutable((string) $row['effective_at']), new DateTimeImmutable((string) $row['recorded_at']));
    }

    /** @return array{resource_key:string,stream_type:string,revision:int,decision:string,effective_at:string,recorded_at:string,revision_checksum:string} */
    private function row(string $resource, string $stream, int $revision, string $decision, DateTimeImmutable $effectiveAt, DateTimeImmutable $recordedAt): array
    {
        $row = ['resource_key' => $resource, 'stream_type' => $stream, 'revision' => $revision, 'decision' => $decision, 'effective_at' => $this->canonical($effectiveAt), 'recorded_at' => $this->canonical($recordedAt)];

        return $row + ['revision_checksum' => $this->checksum($row)];
    }

    /** @param array<string, mixed> $row */
    private function assertValid(array $row, string $stream): void
    {
        try {
            if ((string) $row['stream_type'] !== $stream || ! hash_equals((string) $row['revision_checksum'], $this->checksum($row))) {
                throw new RuntimeException('ContentSeo owner source checksum mismatch.');
            }
        } catch (Throwable $error) {
            throw new RuntimeException('Invalid ContentSeo owner source row.', 0, $error);
        }
    }

    /** @param array<string, mixed> $row */
    private function checksum(array $row): string
    {
        return hash('sha256', implode("\n", [(string) $row['resource_key'], (string) $row['stream_type'], (string) $row['revision'], (string) $row['decision'], $this->canonical(new DateTimeImmutable((string) $row['effective_at'])), $this->canonical(new DateTimeImmutable((string) $row['recorded_at']))]));
    }

    private function canonical(DateTimeImmutable $instant): string
    {
        return $instant->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}
