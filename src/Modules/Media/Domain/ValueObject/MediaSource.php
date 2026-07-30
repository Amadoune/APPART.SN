<?php

namespace Appart\Modules\Media\Domain\ValueObject;

enum MediaSource: string
{
    case Owner = 'owner';
    case Professional = 'professional';
    case Editorial = 'editorial';
    case LegacyMigration = 'legacy_migration';
}
