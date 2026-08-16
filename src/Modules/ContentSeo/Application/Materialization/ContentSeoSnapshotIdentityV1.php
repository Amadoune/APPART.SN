<?php

namespace Appart\Modules\ContentSeo\Application\Materialization;

use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use LogicException;

final readonly class ContentSeoSnapshotIdentityV1
{
    private const string NAMESPACE_URL = '6ba7b811-9dad-11d1-80b4-00c04fd430c8';

    private const string POLICY_ID = 'content-seo-snapshot-materialization-policy-v1';

    public function snapshotId(ListingId $listingId): string
    {
        return $this->uuid5('https://appart.sn/content-seo/public-source-snapshots/'.self::POLICY_ID.'/'.$listingId->value);
    }

    /** @param list<string|int> $facts */
    public function coherenceId(array $facts): string
    {
        return $this->uuid5('https://appart.sn/content-seo/public-source-snapshots/'.self::POLICY_ID.'/coherence/'.hash('sha256', implode('|', array_map(static fn (string|int $fact): string => (string) $fact, $facts))));
    }

    private function uuid5(string $name): string
    {
        $namespace = hex2bin(str_replace('-', '', self::NAMESPACE_URL));
        if (! is_string($namespace)) {
            throw new LogicException('The certified ContentSeo namespace is invalid.');
        }
        $bytes = substr(sha1($namespace.$name, true), 0, 16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x50);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf('%s-%s-%s-%s-%s', substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4), substr($hex, 16, 4), substr($hex, 20));
    }
}
