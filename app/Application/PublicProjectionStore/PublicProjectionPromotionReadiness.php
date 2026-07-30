<?php

namespace App\Application\PublicProjectionStore;

enum PublicProjectionPromotionReadiness: string
{
    case Ready = 'ready';
    case MissingPublicGeographyVersion = 'missing_public_geography_version';
    case MissingPublicMediaVersion = 'missing_public_media_version';
    case MissingPublicGeographyAndMediaVersions = 'missing_public_geography_and_media_versions';
}
