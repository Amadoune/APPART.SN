# Phase 5.2A — Event Catalog Discovery

Ce catalogue est conceptuel. Il ne crée aucun contrat ni événement.

| Owner | Événement candidat | Finalité minimale |
|---|---|---|
| Property Authoring | `property.authoring.initiated.v1` | signaler l'association owner/Property |
| Property Authoring | `property.authoring.ownership_transferred.v1` | réservé à une future règle de transfert certifiée |
| Listing Authoring | `listing.authoring.draft_created.v1` | initialiser les consumers privés |
| Listing Authoring | `listing.authoring.draft_updated.v1` | invalider/recalculer la complétude |
| Listing Authoring | `listing.authoring.completeness_changed.v1` | exposer un état non-PII |
| Listing Ownership | `listing.authoring.delegation_granted.v1` | synchroniser le portefeuille privé |
| Listing Ownership | `listing.authoring.delegation_revoked.v1` | retirer l'accès projeté |
| Listing Authoring | `listing.authoring.submission_requested.v1` | tracer le handoff vers F-01 |

## Minimisation

Payload maximal : eventId, type, version, occurredAt, owner logique, PropertyId
ou ListingId, version résultante et code d'état fermé. Sont interdits : adresse,
description, prix non publié, Account email/téléphone, token, sessionId, claims
et diagnostics internes.

## Compatibilité

- aucun ajout au catalogue Listing Publication V1 ;
- aucun ajout au catalogue Property Lifecycle V1 ;
- aucun ajout au catalogue IAM V1 ;
- destinations, transport, routing, delivery et Outbox seront définis dans des
  jalons ultérieurs ;
- un événement candidat peut être supprimé lors de Contracts si aucun consumer
  démontré ne le justifie.
