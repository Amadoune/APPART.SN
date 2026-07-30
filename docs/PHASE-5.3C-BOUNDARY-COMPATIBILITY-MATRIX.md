# Phase 5.3C — Boundary Compatibility Matrix

## Matrice exhaustive

| Gate | Candidat du dépôt | Public | Versionné pour l'usage | Résultats compatibles | Sans interne | Classification |
|---|---|---:|---:|---:|---:|---|
| IAM authorization | roles/Account Availability | Partiel | Non | Non | Non | Amendement |
| Listing read | `PublicListingQuery` | Oui | Non | Non | Oui | Amendement |
| Media read | `PublicMediaDecisionReader` | Oui | Non | Non | Oui | Amendement |
| Account read | `AccountAvailabilityInspector` | Oui | Non pour modération | Non | Non, résultat détaillé | Amendement |
| ProfessionalProfile read | `ProfessionalPublicStatusReaderV1` | Oui | Oui | Partiel | Oui | Amendement |
| Listing handoff | `ListingPublicationOrchestrator` | Application | Non pour handoff | Non | Contexte interne | Amendement |
| Media handoff | `MediaItemLifecycleOrchestrator` | Application | Non pour handoff | Non | Contexte interne | Amendement |
| Account handoff | `AccountStatusOrchestrator` | Application | Non pour handoff | Non | Contexte interne | Amendement |
| Professional handoff | `ProfessionalStatusOrchestrator` | Application | Non pour handoff | Non | Contexte interne | Amendement |
| Audit append | `AdministrativeActionRegistry`/stores | Non cross-domain | Non | Non | Non | Amendement |

## Lectures et écritures autorisées après amendement

| Consumer 5.3 | Provider owner | Lecture autorisée | Écriture autorisée |
|---|---|---|---|
| Moderation Runtime | IAM | décision d'autorisation minimale | aucune |
| Moderation Runtime | target owner | éligibilité minimale | aucune |
| Moderation Outbox consumer | target owner | résultat du Command | Command public idempotent uniquement |
| Moderation Delivery | Administration Audit | résultat append | record public minimal uniquement |

## Accès définitivement interdits

- `Account`, `Listing`, `MediaItem`, `Professional` ou
  `AdministrativeAction` Aggregate ;
- Registry ou Repository externe ;
- store workflow/context externe ;
- SQL ou projection interne ;
- Controller, route ou Provider externe ;
- snapshot ou version interne non prévue au contrat ;
- transaction ACID cross-domain ;
- reconstruction d'une décision depuis des Events.

## Absence d'ambiguïté

Chaque gate obligatoire est classé `Amendement versionné requis`. Aucune
frontière n'est déclarée implicitement compatible, aucune fonction n'est
silencieusement retirée et aucun amendement n'est ouvert.
