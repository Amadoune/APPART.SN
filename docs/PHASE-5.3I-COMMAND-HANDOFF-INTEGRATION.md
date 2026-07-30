# Phase 5.3I — Command Handoff Integration

## Périmètre

Listing uniquement.

## Statut d'autorité

GO CERTIFIÉ — FERMÉ

Ce statut et l'identité 5.3I sont préservés par
`A-5.3-ROADMAP-SEQUENCING-ALIGNMENT-01`. L'amendement de séquencement ne
modifie aucun élément fonctionnel ou technique du présent dossier.

## Flux certifiable

```text
Outbox moderation.listing-handoff
→ ModerationListingHandoffConsumer
→ décision et dossier owner ModerationReports
→ ModeratorAuthorizationReaderV1(Decide)
→ ListingModerationReaderV1
→ ListingModerationCommandGatewayV1
→ résultat owner-local append-only
→ markDelivered / retry / quarantine
```

Le consumer ne dépend que des frontières certifiées et des stores propriétaires
du dossier, de la décision et du résultat.

## Construction de la commande

Les données proviennent exclusivement du message et des lectures owner-local :

- `caseId` et `decisionId` : Event V1 ;
- `ListingId` : dossier de modération ;
- `actorAccountId` et `targetAction` : décision propriétaire ;
- `commandId` : `eventId` déterministe ;
- checksum : représentation canonique V1 de ListingId, action, commandId et
  occurredAt ;
- occurredAt : Event V1.

Aucune donnée Listing ou IAM n'est reconstruite.

## Politiques fermées

### IAM

Seul `Allowed` poursuit le handoff.

- `Denied` → `AuthorizationDenied`, terminal ;
- `Corrupted` → quarantaine ;
- `DependencyUnavailable` → retry borné.

### Listing Reader

Seul `Eligible` appelle le Gateway.

- `Ineligible` ou `Missing` → `TargetIneligible`, terminal ;
- `Corrupted` → quarantaine ;
- `DependencyUnavailable` → retry borné.

### Gateway

| Résultat Listing | Résultat owner-local | Delivery |
|---|---|---|
| `Applied` | `Applied` | terminale positive |
| `AlreadyApplied` | `AlreadyApplied` | terminale positive |
| `Rejected` | `Rejected` | terminale métier |
| `VersionConflict` | `VersionConflict` | terminale métier |
| `DivergentIntent` | `Quarantined` | quarantaine immédiate |
| `DependencyUnavailable` | `DependencyUnavailable` | retry borné |

## Résultats owner-local

La migration additive 069 crée
`moderation_reports.listing_handoff_results`.

Les révisions sont append-only et contiennent :

- message, dossier et décision ;
- commandId et checksum déterministes ;
- statut fermé ;
- date d'enregistrement.

L'unicité `messageId + status` rend chaque révision idempotente. Le journal ne
possède aucune FK cross-domain, cascade ou trigger.

## Retry, replay et concurrence

Le retry est limité à trois claims. Son délai dépend de l'attempt courant. À
épuisement, la livraison est mise en quarantaine.

Le replay conserve eventId, donc commandId et checksum. Le Gateway Listing
converge vers `AlreadyApplied` et aucune seconde transition F-01 n'est écrite.

Les claims Outbox concurrents sont sérialisés par `FOR UPDATE SKIP LOCKED`. Une
seule livraison peut exécuter le Gateway ; un replay ultérieur démontre
`AlreadyApplied`.

## Atomicité

Les frontières transactionnelles restent indépendantes :

1. décision et append Outbox : transaction ModerationReports certifiée ;
2. Gateway : transaction owner Listing certifiée ;
3. résultat : écriture locale ModerationReports.

Il n'existe aucune transaction ACID cross-domain. Une interruption après la
mutation Listing converge au replay grâce au commandId et checksum stables.

## Runtime

`ModerationListingHandoffServiceProvider` est dédié à 5.3I. Il compose :

- le store PostgreSQL du résultat owner-local ;
- `ModerationListingHandoffConsumer`.

Il ne modifie aucun provider gelé ni Runtime Health.

## Garanties

Aucun accès à Aggregate, Repository, SQL ou Persistence Listing/IAM. Aucun appel
à `ListingPublicationOrchestrator`. Aucun HTTP et aucune ouverture Media,
Account, Professional ou Administration Audit.
