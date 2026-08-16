<?php

namespace App\Console\Commands;

use App\Application\PublicMediaMaterialization\Contract\CatchUpPublicMediaDecisionV2;
use App\Application\PublicMediaMaterialization\Contract\PublicMediaOwnerSourceReaderV2;
use App\Application\PublicMediaSource\Contract\PublicMediaDecisionReader;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Illuminate\Console\Command;
use Throwable;

final class CatchUpPublicMediaDecision extends Command
{
    protected $signature = 'public-media:catch-up {listingId} {--inspect-source}';

    protected $description = 'Materialize or replay the Public Media V2 decision for one Listing.';

    public function handle(CatchUpPublicMediaDecisionV2 $catchUp, PublicMediaOwnerSourceReaderV2 $sources, PublicMediaDecisionReader $reader): int
    {
        try {
            $listingId = ListingId::fromString((string) $this->argument('listingId'));
            if ((bool) $this->option('inspect-source')) {
                $source = $sources->read($listingId);
                $this->line(json_encode(['status' => $source->status->name, 'source' => $source->source], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

                return self::SUCCESS;
            }
            $result = $catchUp->materialize($listingId);
        } catch (Throwable) {
            $this->error(json_encode(['status' => 'invalid_listing_id'], JSON_THROW_ON_ERROR));

            return self::FAILURE;
        }
        $decision = $result->decision;
        $items = $decision === null ? [] : $decision->itemsV2;
        $read = $decision === null ? null : $reader->read($decision->mediaCollectionId);
        $this->line(json_encode([
            'status' => $result->status->value,
            'mediaCollectionId' => $decision?->mediaCollectionId,
            'schemaVersion' => $decision?->schemaVersion,
            'version' => $decision?->revision->version->value,
            'readerStatus' => $read?->status->value,
            'items' => array_map(static fn ($item): array => $item->canonicalData(), $items),
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

        return in_array($result->status->value, ['applied', 'already_applied', 'rejected_obsolete'], true) ? self::SUCCESS : self::FAILURE;
    }
}
