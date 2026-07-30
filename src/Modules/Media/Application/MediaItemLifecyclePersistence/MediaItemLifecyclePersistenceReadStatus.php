<?php

namespace Appart\Modules\Media\Application\MediaItemLifecyclePersistence;

enum MediaItemLifecyclePersistenceReadStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
}
