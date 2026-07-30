<?php

namespace App\Application\MediaIngestionEventRouting;

enum MediaIngestionRoutingDestination: string
{
    case PrivateAudit = 'private_audit';
    case AuthoringStatus = 'authoring_status';
    case MediaAttachment = 'media_attachment';
    case Reconciliation = 'reconciliation';
}
