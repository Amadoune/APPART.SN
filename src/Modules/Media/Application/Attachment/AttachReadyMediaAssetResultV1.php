<?php

namespace Appart\Modules\Media\Application\Attachment;

enum AttachReadyMediaAssetResultV1: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentIntent = 'divergent_intent';
    case PropertyUnavailable = 'property_unavailable';
    case CollectionPropertyConflict = 'collection_property_conflict';
    case MediaIdConflict = 'media_id_conflict';
    case VersionConflict = 'version_conflict';
    case InvalidMedia = 'invalid_media';
    case TemporarilyUnavailable = 'temporarily_unavailable';
}
