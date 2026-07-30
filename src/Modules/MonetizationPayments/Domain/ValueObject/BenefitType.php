<?php

namespace Appart\Modules\MonetizationPayments\Domain\ValueObject;

enum BenefitType: string
{
    case FeaturedPlacement = 'featured_placement';
    case ExtendedVisibility = 'extended_visibility';
    case ProfessionalQuota = 'professional_quota';
}
