<?php

namespace Appart\Modules\Media\Application\Attachment\Contract;

use Appart\Modules\Media\Application\Attachment\AttachReadyMediaAssetCommandV1;
use Appart\Modules\Media\Application\Attachment\AttachReadyMediaAssetResultV1;

interface AttachReadyMediaAssetV1
{
    public function attach(AttachReadyMediaAssetCommandV1 $command): AttachReadyMediaAssetResultV1;
}
