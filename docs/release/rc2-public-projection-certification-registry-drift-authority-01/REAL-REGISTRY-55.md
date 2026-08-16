# Real Registry 55

Toutes les entrées utilisent le consumer id `public-projection` et la payload version 1. Source d'assemblage commune : `app/Providers/PublicProjectionRuntimeServiceProvider.php`.

| # | Module | Type | Source catalogue |
|---:|---|---|---|
| 1 | Identity | `account.status.reactivated@1` | `AccountStatusEventType` |
| 2 | Identity | `account.status.suspended@1` | `AccountStatusEventType` |
| 3 | Administration | `administrative.action.lifecycle.approval_requested@1` | `AdministrativeActionLifecycleEventType` |
| 4 | Administration | `administrative.action.lifecycle.approved@1` | `AdministrativeActionLifecycleEventType` |
| 5 | Administration | `administrative.action.lifecycle.recorded@1` | `AdministrativeActionLifecycleEventType` |
| 6 | Administration | `administrative.action.lifecycle.rejected@1` | `AdministrativeActionLifecycleEventType` |
| 7 | Reconstruction | `content_seo.reconstruction.requested@1` | provider, catalogue fermé |
| 8 | Lead | `lead.lifecycle.closed@1` | `LeadLifecycleEventType` |
| 9 | Lead | `lead.lifecycle.delivered@1` | `LeadLifecycleEventType` |
| 10 | Lead | `lead.lifecycle.rejected@1` | `LeadLifecycleEventType` |
| 11 | Listing | `listing.publication.archived@1` | `ListingPublicationEventType` |
| 12 | Listing | `listing.publication.changes_requested@1` | `ListingPublicationEventType` |
| 13 | Listing | `listing.publication.expired@1` | `ListingPublicationEventType` |
| 14 | Listing | `listing.publication.material_change_review_started@1` | `ListingPublicationEventType` |
| 15 | Listing | `listing.publication.published@1` | `ListingPublicationEventType` |
| 16 | Listing | `listing.publication.reinstated@1` | `ListingPublicationEventType` |
| 17 | Listing | `listing.publication.rejected@1` | `ListingPublicationEventType` |
| 18 | Listing | `listing.publication.renewal_review_started@1` | `ListingPublicationEventType` |
| 19 | Listing | `listing.publication.renewed@1` | `ListingPublicationEventType` |
| 20 | Listing | `listing.publication.republication_review_started@1` | `ListingPublicationEventType` |
| 21 | Listing | `listing.publication.resubmitted@1` | `ListingPublicationEventType` |
| 22 | Listing | `listing.publication.review_started@1` | `ListingPublicationEventType` |
| 23 | Listing | `listing.publication.submitted@1` | `ListingPublicationEventType` |
| 24 | Listing | `listing.publication.suspended@1` | `ListingPublicationEventType` |
| 25 | Listing | `listing.publication.withdrawn@1` | `ListingPublicationEventType` |
| 26 | Reconstruction | `listing.reconstruction.requested@1` | provider, catalogue fermé |
| 27 | Media | `media.item.lifecycle.archived@1` | `MediaItemLifecycleEventType` |
| 28 | Media | `media.item.lifecycle.removed@1` | `MediaItemLifecycleEventType` |
| 29 | Reconstruction | `media.reconstruction.requested@1` | provider, catalogue fermé |
| 30 | Geography | `place.lifecycle.disabled@1` | `PlaceLifecycleEventType` |
| 31 | Geography | `place.lifecycle.enabled@1` | `PlaceLifecycleEventType` |
| 32 | Geography | `place.lifecycle.merged@1` | `PlaceLifecycleEventType` |
| 33 | Geography refresh | `place.lifecycle.renamed@1` | provider, entrée explicite |
| 34 | Professionals | `professional.status.reactivated@1` | `ProfessionalStatusEventType` |
| 35 | Professionals | `professional.status.suspended@1` | `ProfessionalStatusEventType` |
| 36 | Property | `property.lifecycle.activated@1` | `PropertyLifecycleEventType` |
| 37 | Property | `property.lifecycle.archived@1` | `PropertyLifecycleEventType` |
| 38 | Property | `property.lifecycle.availability_restored@1` | `PropertyLifecycleEventType` |
| 39 | Property | `property.lifecycle.decommissioned@1` | `PropertyLifecycleEventType` |
| 40 | Property | `property.lifecycle.maintenance_completed@1` | `PropertyLifecycleEventType` |
| 41 | Property | `property.lifecycle.maintenance_started@1` | `PropertyLifecycleEventType` |
| 42 | Property | `property.lifecycle.marked_unavailable@1` | `PropertyLifecycleEventType` |
| 43 | Reconstruction | `property.reconstruction.requested@1` | provider, catalogue fermé |
| 44 | Reservation | `reservation.lifecycle.cancelled_from_confirmed@1` | `ReservationLifecycleEventType` |
| 45 | Reservation | `reservation.lifecycle.cancelled_from_draft@1` | `ReservationLifecycleEventType` |
| 46 | Reservation | `reservation.lifecycle.cancelled_from_requested@1` | `ReservationLifecycleEventType` |
| 47 | Reservation | `reservation.lifecycle.cancelled_in_progress@1` | `ReservationLifecycleEventType` |
| 48 | Reservation | `reservation.lifecycle.completed@1` | `ReservationLifecycleEventType` |
| 49 | Reservation | `reservation.lifecycle.confirmed@1` | `ReservationLifecycleEventType` |
| 50 | Reservation | `reservation.lifecycle.expired_from_confirmed@1` | `ReservationLifecycleEventType` |
| 51 | Reservation | `reservation.lifecycle.expired_from_requested@1` | `ReservationLifecycleEventType` |
| 52 | Reservation | `reservation.lifecycle.rejected@1` | `ReservationLifecycleEventType` |
| 53 | Reservation | `reservation.lifecycle.started@1` | `ReservationLifecycleEventType` |
| 54 | Reservation | `reservation.lifecycle.submitted@1` | `ReservationLifecycleEventType` |
| 55 | Reconstruction | `search.reconstruction.requested@1` | provider, catalogue fermé |

Le classement est lexicographique par type; l'élément différentiel est le n°33, pas nécessairement la dernière ligne du tri.
