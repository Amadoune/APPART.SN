<?php

namespace Tests\Unit\PublicGeographySource;

use App\Application\PublicGeographySource\PublicGeographyDecisionV2;
use App\Infrastructure\PublicGeographySource\PostgreSql\PostgreSqlPublicGeographyMapper;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PostgreSqlPublicGeographyMapperV2Test extends TestCase
{
    public function test_v1_remains_readable_and_v2_is_discriminated_without_url(): void
    {
        $v1 = '{"locality":"Dakar","breadcrumb":[{"label":"Dakar","url":"https://appart.sn/dakar"}]}';
        self::assertSame('Dakar', (new PostgreSqlPublicGeographyMapper)->toDecision($this->row('place:dakar', $v1))->locality);

        $v2 = $this->payload();
        $decision = (new PostgreSqlPublicGeographyMapper)->toDecision($this->row('city:dakar', $v2));
        self::assertInstanceOf(PublicGeographyDecisionV2::class, $decision);
        self::assertSame($v2, $decision->canonicalPayload());
    }

    public function test_unknown_schema_is_rejected_fail_closed(): void
    {
        $this->expectException(RuntimeException::class);
        (new PostgreSqlPublicGeographyMapper)->toDecision($this->row('city:dakar', '{"schemaVersion":"unknown"}'));
    }

    public function test_url_in_v2_item_is_rejected_fail_closed(): void
    {
        $this->expectException(RuntimeException::class);
        $payload = str_replace('"type":"city"', '"type":"city","url":"#"', $this->payload());
        (new PostgreSqlPublicGeographyMapper)->toDecision($this->row('city:dakar', $payload));
    }

    /** @return array<string,int|string> */
    private function row(string $placeId, string $payload): array
    {
        return [
            'place_id' => $placeId,
            'version' => 1,
            'causation_key' => 'mapper:v2:test',
            'revision_checksum' => hash('sha256', $payload),
            'payload' => $payload,
            'payload_checksum' => hash('sha256', $payload),
        ];
    }

    private function payload(): string
    {
        return '{"schemaVersion":"public-geography-place-representation-v2","terminalPlaceId":"city:dakar","status":"available","locality":"Dakar","breadcrumb":[{"placeId":"city:dakar","type":"city","officialName":"Dakar","parentPlaceId":null,"aggregateVersion":1}],"revisionVector":[{"placeId":"city:dakar","aggregateVersion":1}]}';
    }
}
