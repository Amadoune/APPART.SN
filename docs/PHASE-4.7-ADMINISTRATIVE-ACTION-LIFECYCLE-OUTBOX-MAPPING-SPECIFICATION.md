# Phase 4.7 — Administrative Action Lifecycle Outbox Mapping Specification

## Catalogue Delivery

| Event type | Owner | Aggregate | Version | Payload |
|---|---|---|---:|---|
| `administrative.action.lifecycle.recorded` | `AdministrationAudit` | `AdministrativeActionLifecycle` | 1 | `AdministrativeActionLifecycleDeliveryPayload` |
| `administrative.action.lifecycle.approval_requested` | `AdministrationAudit` | `AdministrativeActionLifecycle` | 1 | `AdministrativeActionLifecycleDeliveryPayload` |
| `administrative.action.lifecycle.approved` | `AdministrationAudit` | `AdministrativeActionLifecycle` | 1 | `AdministrativeActionLifecycleDeliveryPayload` |
| `administrative.action.lifecycle.rejected` | `AdministrationAudit` | `AdministrativeActionLifecycle` | 1 | `AdministrativeActionLifecycleDeliveryPayload` |

L'identité d'agrégat provient mécaniquement de
`payload.event.payload.actionId`. La version causale et l'index restent ceux du
message Delivery.

## Restauration PostgreSQL

Le mapper générique reconnaît les quatre types via
`AdministrativeActionLifecycleEventType::tryFrom()`, puis délègue
exclusivement à `AdministrativeActionLifecycleDeliveryPayload::restore()`.

La restauration doit conserver exactement :

- les octets de `canonicalEvent` ;
- le checksum du payload Delivery ;
- `eventId` ;
- `messageId` ;
- les métadonnées génériques du message.

Toute charge non conforme est refusée par les contrats de restauration
certifiés ; aucun fallback ni enrichissement n'est autorisé.

## Isolation

Le Writer résout `AdministrationAudit → administration_audit`. Le Reader filtre
par `source_module = AdministrationAudit`. Aucun enregistrement d'un autre owner
ne peut être restauré comme message Administrative Action Lifecycle.
