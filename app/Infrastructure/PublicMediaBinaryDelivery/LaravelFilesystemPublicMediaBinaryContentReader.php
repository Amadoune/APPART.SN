<?php

namespace App\Infrastructure\PublicMediaBinaryDelivery;

use App\Application\PublicMediaBinaryDelivery\Contract\PublicMediaBinaryContentReader;
use App\Application\PublicMediaBinaryDelivery\PublicMediaBinaryContentResult;
use App\Application\PublicMediaBinaryDelivery\PublicMediaBinaryContentStatus;
use Illuminate\Contracts\Filesystem\Filesystem;
use Throwable;

final readonly class LaravelFilesystemPublicMediaBinaryContentReader implements PublicMediaBinaryContentReader
{
    public function __construct(private Filesystem $disk) {}

    public function read(string $ownerId, string $mediaId, string $checksum, int $bytes): PublicMediaBinaryContentResult
    {
        try {
            $key = 'owners/'.strtolower($ownerId).'/assets/'.strtolower($mediaId);
            if (! $this->disk->exists($key)) {
                return new PublicMediaBinaryContentResult(PublicMediaBinaryContentStatus::Missing);
            }
            $binary = $this->disk->get($key);
            if (! is_string($binary)
                || strlen($binary) !== $bytes
                || ! hash_equals($checksum, hash('sha256', $binary))) {
                return new PublicMediaBinaryContentResult(PublicMediaBinaryContentStatus::Corrupted);
            }
            $stream = fopen('php://temp', 'w+b');
            if (! is_resource($stream) || fwrite($stream, $binary) !== $bytes || ! rewind($stream)) {
                if (is_resource($stream)) {
                    fclose($stream);
                }

                return new PublicMediaBinaryContentResult(PublicMediaBinaryContentStatus::DependencyUnavailable);
            }

            return new PublicMediaBinaryContentResult(PublicMediaBinaryContentStatus::Found, $stream);
        } catch (Throwable) {
            return new PublicMediaBinaryContentResult(PublicMediaBinaryContentStatus::DependencyUnavailable);
        }
    }
}
