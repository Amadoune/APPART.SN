<?php

namespace App\Application\ModerationEventRouting;

enum ModerationRoutingDestination: string
{
    case QueueProjection = 'moderation.queue';
    case CaseTimeline = 'moderation.timeline';
    case DeliveryObservation = 'moderation.delivery-observation';
    case ListingHandoff = 'moderation.listing-handoff';
}
