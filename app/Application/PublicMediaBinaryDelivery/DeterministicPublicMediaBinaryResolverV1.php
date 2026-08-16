<?php

namespace App\Application\PublicMediaBinaryDelivery;

use App\Application\PublicMediaBinaryDelivery\Contract\PublicMediaBinaryContentReader;
use App\Application\PublicMediaBinaryDelivery\Contract\PublicMediaBinaryOwnerSourceReader;
use App\Application\PublicMediaBinaryDelivery\Contract\ResolvePublicMediaBinaryV1;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Throwable;

final readonly class DeterministicPublicMediaBinaryResolverV1 implements ResolvePublicMediaBinaryV1
{
    private const CONTENT_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function __construct(
        private PublicMediaBinaryOwnerSourceReader $sources,
        private PublicMediaBinaryContentReader $content,
    ) {}

    public function resolve(string $mediaId, int $assetVersion): PublicMediaBinaryResult
    {
        try {
            $canonicalId = MediaId::fromString($mediaId)->value;
        } catch (Throwable) {
            return new PublicMediaBinaryResult(PublicMediaBinaryStatus::NotFound);
        }
        if ($assetVersion < 1) {
            return new PublicMediaBinaryResult(PublicMediaBinaryStatus::NotFound);
        }

        $read = $this->sources->read($canonicalId);
        if ($read->status === PublicMediaBinarySourceStatus::DependencyUnavailable) {
            return new PublicMediaBinaryResult(PublicMediaBinaryStatus::DependencyUnavailable);
        }
        if ($read->status === PublicMediaBinarySourceStatus::Corrupted) {
            return new PublicMediaBinaryResult(PublicMediaBinaryStatus::Corrupted);
        }
        $source = $read->source;
        if ($read->status !== PublicMediaBinarySourceStatus::Found || $source === null) {
            return new PublicMediaBinaryResult(PublicMediaBinaryStatus::NotFound);
        }
        if ($source->mediaId !== $canonicalId
            || $source->mediaStatus !== 'active'
            || $source->assetState !== 'ready'
            || $source->assetVersion !== $assetVersion
            || ! in_array($source->attachmentResult, ['applied', 'already_applied'], true)
            || ! $source->listingPublished) {
            return new PublicMediaBinaryResult(PublicMediaBinaryStatus::NotFound);
        }
        if (! in_array($source->contentType, self::CONTENT_TYPES, true)
            || preg_match('/^[0-9a-f]{64}$/D', $source->checksum) !== 1
            || $source->bytes < 1) {
            return new PublicMediaBinaryResult(PublicMediaBinaryStatus::Corrupted);
        }

        $content = $this->content->read($source->ownerId, $canonicalId, $source->checksum, $source->bytes);

        return match ($content->status) {
            PublicMediaBinaryContentStatus::Found => is_resource($content->stream)
                ? new PublicMediaBinaryResult(PublicMediaBinaryStatus::Found, $content->stream, $source->contentType, $source->bytes, $source->checksum)
                : new PublicMediaBinaryResult(PublicMediaBinaryStatus::Corrupted),
            PublicMediaBinaryContentStatus::Missing => new PublicMediaBinaryResult(PublicMediaBinaryStatus::NotFound),
            PublicMediaBinaryContentStatus::Corrupted => new PublicMediaBinaryResult(PublicMediaBinaryStatus::Corrupted),
            PublicMediaBinaryContentStatus::DependencyUnavailable => new PublicMediaBinaryResult(PublicMediaBinaryStatus::DependencyUnavailable),
        };
    }
}
