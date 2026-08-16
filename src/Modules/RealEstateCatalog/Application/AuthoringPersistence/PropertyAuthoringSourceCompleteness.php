<?php

namespace Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence;

enum PropertyAuthoringSourceCompleteness: string
{
    case CompleteForPromotion = 'complete_for_promotion';
    case IncompleteForPromotion = 'incomplete_for_promotion';
}
