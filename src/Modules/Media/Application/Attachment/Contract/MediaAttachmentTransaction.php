<?php

namespace Appart\Modules\Media\Application\Attachment\Contract;

use Closure;

interface MediaAttachmentTransaction
{
    public function run(Closure $operation): mixed;
}
