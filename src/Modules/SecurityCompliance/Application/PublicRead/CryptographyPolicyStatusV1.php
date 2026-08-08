<?php

namespace Appart\Modules\SecurityCompliance\Application\PublicRead;

enum CryptographyPolicyStatusV1: string
{
    case Available = 'available';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
