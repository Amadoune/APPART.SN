<?php

namespace App\Console\Commands;

use Appart\Modules\ContentSeo\Application\Materialization\ContentSeoMaterializationStatus;
use Appart\Modules\ContentSeo\Application\Materialization\Contract\CatchUpContentSeoSnapshotV1;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;
use Illuminate\Console\Command;

final class MaterializeContentSeoSnapshot extends Command
{
    protected $signature = 'appart:content-seo:materialize {listingId}';

    protected $description = 'Materialize one certified Content/SEO source snapshot.';

    public function handle(CatchUpContentSeoSnapshotV1 $catchUp): int
    {
        $result = $catchUp->catchUp(ListingId::fromString((string) $this->argument('listingId')));
        $this->line($result->status->value);

        return in_array($result->status, [ContentSeoMaterializationStatus::Applied, ContentSeoMaterializationStatus::AlreadyApplied], true) ? self::SUCCESS : self::FAILURE;
    }
}
