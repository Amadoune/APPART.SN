<?php

namespace App\Application\MediaIngestionEventOutbox;

enum MediaIngestionOutboxWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentMessage = 'divergent_message';
}
