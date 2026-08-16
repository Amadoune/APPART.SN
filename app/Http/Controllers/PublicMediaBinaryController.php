<?php

namespace App\Http\Controllers;

use App\Application\PublicMediaBinaryDelivery\Contract\ResolvePublicMediaBinaryV1;
use App\Application\PublicMediaBinaryDelivery\PublicMediaBinaryStatus;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PublicMediaBinaryController extends Controller
{
    public function __construct(private readonly ResolvePublicMediaBinaryV1 $resolver) {}

    public function __invoke(string $mediaId, string $assetVersion): Response|StreamedResponse
    {
        $version = filter_var($assetVersion, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (! is_int($version)) {
            return $this->empty(404);
        }
        $result = $this->resolver->resolve($mediaId, $version);
        if ($result->status === PublicMediaBinaryStatus::DependencyUnavailable) {
            return $this->empty(503);
        }
        if ($result->status !== PublicMediaBinaryStatus::Found
            || ! is_resource($result->stream)
            || $result->contentType === null
            || $result->bytes === null) {
            return $this->empty(404);
        }

        $stream = $result->stream;

        return new StreamedResponse(static function () use ($stream): void {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $result->contentType,
            'Content-Length' => (string) $result->bytes,
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }

    private function empty(int $status): Response
    {
        return new Response('', $status, [
            'Cache-Control' => 'no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
