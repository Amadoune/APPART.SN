<?php

namespace App\Application\ModerationAtomicOperation;

enum ModerationAtomicDecision: string
{
    case Commit = 'commit';
    case Rollback = 'rollback';
}
