<?php

namespace App\Application\PublicProjectionOutbox;

enum PublicProjectionOutboxRetryClassification: string
{
    case Transient = 'transient';
    case Permanent = 'permanent';
    case SourceNotReady = 'source_not_ready';
    case SequenceGap = 'sequence_gap';
}
