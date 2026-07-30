<?php

namespace Appart\Modules\ContentSeo\Application\Snapshot;

enum ContentSeoSnapshotWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case RejectedObsolete = 'rejected_obsolete';
    case Divergent = 'divergent';
}
