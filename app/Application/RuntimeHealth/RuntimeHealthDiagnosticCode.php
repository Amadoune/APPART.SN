<?php

namespace App\Application\RuntimeHealth;

enum RuntimeHealthDiagnosticCode: string
{
    case ComponentAbsent = 'component_absent';
    case ImplementationNotRegistered = 'implementation_not_registered';
    case InvalidConfiguration = 'invalid_configuration';
    case IncompatibleContract = 'incompatible_contract';
    case DependencyMissing = 'dependency_missing';
}
