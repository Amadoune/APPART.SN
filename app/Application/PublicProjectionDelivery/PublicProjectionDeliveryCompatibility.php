<?php

namespace App\Application\PublicProjectionDelivery;

enum PublicProjectionDeliveryCompatibility: string
{
    case Supported = 'supported';
    case DeprecatedButSupported = 'deprecated_but_supported';
    case UnsupportedVersion = 'unsupported_version';
    case UnsupportedType = 'unsupported_type';
}
