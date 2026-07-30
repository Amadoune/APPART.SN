# Phase 5.2A — Event Contracts V1

Ces contrats restent documentaires ; aucune classe Event n'est créée.

## Enveloppe

`eventId`, `eventType`, `eventVersion=1`, `owner`, `aggregateId`,
`aggregateVersion`, `occurredAt`, `intentId`, `payload`, `checksum`.

`eventId` et checksum sont déterministes à partir de données canoniques non-PII.

## Catalogue minimal retenu

| Type | Owner | Payload |
|---|---|---|
| `property.authoring.initiated.v1` | PropertyAuthoring | propertyId, version |
| `listing.authoring.draft_created.v1` | ListingAuthoringDraft | listingId, propertyId, version |
| `listing.authoring.draft_updated.v1` | ListingAuthoringDraft | listingId, version, changedFieldCodes |
| `listing.authoring.delegation_changed.v1` | ListingOwnership | listingId, ownershipVersion, changeCode |
| `listing.authoring.submission_requested.v1` | ListingAuthoringDraft | listingId, version, completenessPolicyVersion |

Les candidats ownership transfer et completeness changed du Discovery sont
écartés en V1 faute de consumer indispensable démontré.

## Replay

- même eventId et checksum : `AlreadyConsumed` ;
- même eventId et checksum différent : `DivergentEvent`, quarantaine ;
- consumer retryable : backoff borné ;
- erreur contractuelle : quarantaine immédiate ;
- replay n'exécute jamais deux fois une commande owner.

## Confidentialité

Aucun AccountId de délégué, adresse, texte éditorial, prix privé, PII, token,
sessionId ou diagnostic interne dans les payloads.
