# Phase 5.3J — HTTP & Security Discovery

## Statut

NO GO CERTIFIÉ — FERMÉ

5.3K et 5.3L restent NON OUVERTS.

## Objet

Ce dossier confronte la surface HTTP candidate à l'état réel du dépôt. Il ne
crée aucun Controller, Request, Resource, Route, Middleware, provider, contrat
PHP ou test.

## État réel du dépôt

### Frontières exécutables

| Besoin | Frontière réelle | État |
|---|---|---|
| session IAM | `RequireIdentityAccessSession` | exécutable, fail-closed, fournit `iam_account_id` |
| autorisation modérateur | `ModeratorAuthorizationReaderV1` | exécutable et lié au container |
| mutations de modération | `ModerationCaseOrchestratorV1` | exécutable, six Commands V1 |
| Runtime propriétaire | `ModerationRuntimeV1` | exécutable |
| Queue claim | `ModerationQueueRuntimeV1` via l'orchestrateur | exécutable et idempotent |
| cible Listing | `ListingModerationReaderV1` | frontière certifiée et exécutable |
| handoff Listing | `ListingModerationCommandGatewayV1` | consommé exclusivement par 5.3I |
| événements et livraison | catalogue V1, Delivery et Outbox 5.3G/5.3H | certifiés ; aucun changement requis |

Les six Commands concrets disponibles sont :

- `SubmitModerationReportV1` ;
- `ValidateModerationReportV1` ;
- `RecordModerationFindingV1` ;
- `IssueModerationDecisionV1` ;
- `CloseModerationCaseV1` ;
- `ClaimModerationQueueItemV1`.

Le catalogue d'exécution commun est fermé par `ModerationCommandStatus`.

### Frontières uniquement documentaires

Les Queries suivantes sont définies par les documents 5.3B, mais aucune
interface PHP, aucun résultat typé, aucun reader owner-scoped et aucun binding
Laravel correspondant n'existent :

- `ReadOwnModerationReportV1` ;
- `ReadModerationQueueV1` ;
- `ReadModerationCaseV1` ;
- `ReadModerationDecisionV1`.

La recherche exhaustive ne trouve ces noms que dans :

- `PHASE-5.3B-COMMANDS-AND-QUERIES.md` ;
- `PHASE-5.3B-RESULT-MATRIX.md` ;
- `PHASE-5.3B-CONTRACTS-FOUNDATION.md` ;
- le Blueprint 5.3.

### Routes et Controllers

Aucune route, aucun Controller et aucun Request ModerationReports n'existent.
Cette absence est conforme au statut de Discovery.

## Analyse de compatibilité

### Mutations

Les mutations peuvent conceptuellement être adaptées sans modifier les
fondations certifiées :

- `actorAccountId` dérivé de `iam_account_id` ;
- `intentId` dérivé d'un `Idempotency-Key` UUID ;
- `expectedVersion` fourni explicitement et validé ;
- `occurredAt` et `policyVersion` inclus conformément aux Commands ;
- autorisation privée contrôlée par `ModeratorAuthorizationReaderV1` avant
  l'appel de l'orchestrateur ;
- résultats convertis par un mapper fermé, sans diagnostic interne.

La soumission publique doit être limitée à la cible Listing tant que les autres
frontières cibles restent non certifiées. Le client ne peut fournir ni acteur,
ni rôle, ni capacité IAM.

### Lectures

Une couche HTTP ne peut pas utiliser directement :

- `ModerationCaseStore` ;
- `ModerationDecisionStore` ;
- `ModerationQueueStore` ;
- leurs états de persistence ;
- PostgreSQL ou un mapper Infrastructure.

Ces composants exposent des états owner-internal et non les vues filtrées
certifiées par 5.3B. Les employer depuis HTTP contournerait les Queries
publiques, exposerait potentiellement acteurs, preuves et diagnostics, et
dupliquerait la décision de visibilité.

## Manque bloquant

Le blocage unique est l'absence d'une fondation exécutable pour les quatre
Queries V1 certifiées. Une extension versionnée préalable doit autoriser :

1. les quatre ports de Query Application V1 ;
2. leurs Commands/criteria et résultats fermés déjà documentés ;
3. des readers owner-scoped produisant uniquement des vues filtrées ;
4. des bindings Laravel uniques, lazy et singleton ;
5. la séparation entre vue reporter, queue, dossier privé et décision ;
6. l'anti-énumération de `ReadOwnModerationReportV1` ;
7. des tests Unit, Architecture et PostgreSQL ciblés.

Identifiant recommandé :

`A-5.3-MODERATION-HTTP-READ-BOUNDARIES-01`

Cet amendement ne devra créer aucun HTTP. Il devra rester owner
ModerationReports, read-only et additif, sans modifier les migrations 063–069
ni les fondations gelées.

## Frontières de sécurité réutilisables

- cookie et inspection de session IAM via `RequireIdentityAccessSession` ;
- autorisation `Allowed`, `Denied`, `Corrupted`,
  `DependencyUnavailable`, tous les résultats autres que `Allowed` étant
  fail-closed ;
- capacités IAM fermées `Report`, `Validate`, `Investigate`, `Decide`, `Audit` ;
- validation stricte et refus des champs inconnus inspirés des adaptateurs 5.1
  et 5.2A, sans les modifier ;
- rate limiting par clé HMAC sans PII ;
- `Cache-Control: no-store` et `X-Content-Type-Options: nosniff` ;
- CSRF Laravel conservé pour toutes les mutations de session ;
- aucune exemption CSRF candidate.

## Dépendances

| Source candidate | Cible | Décision |
|---|---|---|
| HTTP 5.3J | `ModerationCaseOrchestratorV1` | autorisée |
| HTTP 5.3J | `ModeratorAuthorizationReaderV1` | autorisée pour les opérations privées |
| HTTP 5.3J | futures Queries V1 owner ModerationReports | requise, actuellement absente |
| HTTP 5.3J | stores, PDO, SQL, Aggregate | interdite |
| HTTP 5.3J | `ListingModerationCommandGatewayV1` | interdite ; le handoff reste 5.3I |
| HTTP 5.3J | Event, Delivery, Outbox | interdite |

## Décision d'autorité

**PHASE 5.3J — HTTP & SECURITY DISCOVERY — NO GO CERTIFIÉ — FERMÉ.**

L'implémentation HTTP serait partielle ou obligerait un accès interdit aux
stores. La prochaine étape strictement autorisable est l'ouverture explicite
de `A-5.3-MODERATION-HTTP-READ-BOUNDARIES-01`. 5.3J reste ouverte au niveau
Discovery ; aucun travail HTTP technique n'est autorisé avant certification de
cette frontière.
