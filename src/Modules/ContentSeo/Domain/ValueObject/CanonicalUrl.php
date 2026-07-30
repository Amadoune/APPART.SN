<?php

namespace Appart\Modules\ContentSeo\Domain\ValueObject;

use Appart\Modules\ContentSeo\Domain\Exception\InvalidSeoValue;

final readonly class CanonicalUrl
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim($value);
        $parts = parse_url($value);
        $host = strtolower($parts['host'] ?? '');
        if (($parts['scheme'] ?? null) !== 'https'
            || ! in_array($host, ['appart.sn', 'www.appart.sn'], true)
            || isset($parts['query'])
            || isset($parts['fragment'])
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['port'])) {
            throw InvalidSeoValue::field('canonical_url');
        }
        $decodedPath = rawurldecode((string) ($parts['path'] ?? '/'));
        $segments = [];
        foreach (preg_split('#/+#', str_replace('\\', '/', $decodedPath)) ?: [] as $segment) {
            $segment = mb_strtolower(trim($segment));
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($segments);

                continue;
            }
            $segments[] = rawurlencode($segment);
        }
        if ($segments === []) {
            throw InvalidSeoValue::field('canonical_url');
        }
        $path = '/'.implode('/', $segments);

        return new self("https://appart.sn{$path}");
    }
}
