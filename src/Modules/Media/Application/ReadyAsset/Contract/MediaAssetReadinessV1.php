<?php

namespace Appart\Modules\Media\Application\ReadyAsset\Contract;

use Appart\Modules\Media\Application\ReadyAsset\MediaAssetReadinessRequest;
use Appart\Modules\Media\Application\ReadyAsset\MediaAssetReadinessResult;

interface MediaAssetReadinessV1
{
    public function makeReady(MediaAssetReadinessRequest $request): MediaAssetReadinessResult;
}
