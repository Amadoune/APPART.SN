<?php

namespace Appart\Modules\SearchDiscovery\Application\Materialization;

use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchIndexId;
use LogicException;

final readonly class PublicSearchDecisionIdentityV1
{
    private const string NAMESPACE_URL = '6ba7b811-9dad-11d1-80b4-00c04fd430c8';

    private const string NAME_PREFIX = 'https://appart.sn/search-discovery/public-search-decisions/';

    public function issue(ListingId $listingId, string $policyId): SearchIndexId
    {
        $namespace = hex2bin(str_replace('-', '', self::NAMESPACE_URL));
        if (! is_string($namespace)) {
            throw new LogicException('The certified Search decision namespace is invalid.');
        }
        $name = self::NAME_PREFIX.$policyId.'/'.$listingId->value;
        $bytes = substr(sha1($namespace.$name, true), 0, 16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x50);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);
        $hex = bin2hex($bytes);

        return SearchIndexId::fromString(sprintf('%s-%s-%s-%s-%s', substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4), substr($hex, 16, 4), substr($hex, 20)));
    }
}
