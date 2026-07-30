<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow;

enum ListingPublicationAction: string
{
    case Submit = 'submit';
    case BeginReview = 'begin_review';
    case ApproveAndPublish = 'approve_and_publish';
    case RequestChanges = 'request_changes';
    case Reject = 'reject';
    case Withdraw = 'withdraw';
    case ReviewMaterialChange = 'review_material_change';
    case Suspend = 'suspend';
    case Expire = 'expire';
    case Reinstate = 'reinstate';
    case ReviewRenewal = 'review_renewal';
    case RenewDirectly = 'renew_directly';
    case ApproveRepublication = 'approve_republication';
    case Archive = 'archive';
    case Unknown = 'unknown';
}
