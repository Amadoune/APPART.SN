<?php

namespace App\Application\PropertyListingResolution;

enum PropertyListingsDiagnostic: string
{
    case None = 'none';
    case InvalidPropertyIdentity = 'invalid_property_identity';
    case InvalidLimit = 'invalid_limit';
    case InvalidCheckpoint = 'invalid_checkpoint';
    case CheckpointForAnotherProperty = 'checkpoint_for_another_property';
    case PersistedIdentityNotMappable = 'persisted_identity_not_mappable';
}
