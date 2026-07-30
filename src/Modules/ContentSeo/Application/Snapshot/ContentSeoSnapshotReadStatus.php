<?php

namespace Appart\Modules\ContentSeo\Application\Snapshot;

enum ContentSeoSnapshotReadStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
}
