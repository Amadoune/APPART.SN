<?php

namespace Appart\Modules\Professionals\Application\ProfessionalProfilePersistence;

enum PublicProfileVisibility: string
{
    case Draft = 'draft';
    case Visible = 'visible';
    case Hidden = 'hidden';
}
