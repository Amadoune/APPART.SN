# Phase 5.3C — Moderation Command Handoff Audit

## Contrat attendu

Chaque target owner doit exposer un Command Gateway public, versionné et
idempotent avec :

`Applied`, `AlreadyApplied`, `Rejected`, `DivergentIntent`,
`VersionConflict`, `DependencyUnavailable`.

Le Command doit porter une identité et un checksum déterministes. Moderation ne
peut ni construire un contexte interne ni appeler un store cible.

## Listing

### État réel

`ListingPublicationOrchestrator` expose les actions du lifecycle, dont
`Suspend`, et retourne :

`Applied`, `AlreadyApplied`, `Denied`, `ConcurrencyConflict`,
`PersistenceFailure`.

### Incompatibilités

- interface non suffixée V1 pour ce handoff ;
- absence de `DivergentIntent` et `DependencyUnavailable` ;
- requête attendue construite avec le contexte interne du workflow ;
- aucune attribution contractuelle d'une décision de modération ;
- mapping de `ConcurrencyConflict` vers `VersionConflict` non certifié.

### Décision

**Amendement `A-5.3-LISTING-MODERATION-BOUNDARY-01` requis.**

Il devra adapter une demande de sanction sans modifier les transitions F-01.

## Media

### État réel

`MediaItemLifecycleOrchestrator` expose `Remove` et `Archive`, avec :

`Applied`, `AlreadyApplied`, `Missing`, `VersionConflict`, `Denied`,
`StateConflict`, `ContextDivergence`, `PersistenceCorrupted`.

### Incompatibilités

- aucune commande V1 dédiée au handoff ;
- le contexte lifecycle interne est requis ;
- absence du couple intent/checksum public attendu ;
- `DependencyUnavailable` absent ;
- `ContextDivergence` n'est pas `DivergentIntent`.

### Décision

**Amendement `A-5.3-MEDIA-MODERATION-BOUNDARY-01` requis.**

## Account

### État réel

`AccountStatusOrchestrator` applique `Suspend` ou `Reactivate` et expose :

`Applied`, `AlreadyInState`, `AccountMissing`, `VersionConflict`,
`InvalidContext`, `PersistenceRejected`, `PersistenceCorrupted`,
`InspectionCorrupted`.

### Incompatibilités

- aucune frontière de sanction de modération ;
- absence de `DivergentIntent` et du résultat public
  `DependencyUnavailable` ;
- contexte Account Status V1 propriétaire requis ;
- `AlreadyInState` n'est pas le contrat `AlreadyApplied` de handoff ;
- une adaptation implicite modifierait la sémantique gelée F-17.

### Décision

**Amendement `A-5.3-ACCOUNT-MODERATION-BOUNDARY-01` requis.**

## Professional

### État réel

`ProfessionalStatusOrchestrator` expose `Suspend` et `Reactivate` avec :

`Applied`, `AlreadyApplied`, `Missing`, `VersionConflict`, `Denied`,
`StateConflict`, `ContextDivergence`, `PersistenceCorrupted`.

### Incompatibilités

- aucune commande publique V1 attribuable à une décision 5.3 ;
- contexte lifecycle interne requis ;
- `DivergentIntent` et `DependencyUnavailable` absents ;
- la cible Blueprint est ProfessionalProfile tandis que la mutation appartient
  au Professional Status owner ;
- aucun droit implicite de sanction n'est accordé à 5.3.

### Décision

**Amendement `A-5.3-PROFESSIONAL-MODERATION-BOUNDARY-01` requis.**

## Règle commune

Les orchestrateurs existants peuvent constituer une implémentation interne
future derrière une frontière certifiée par leur owner. Moderation ne peut pas
les consommer directement. Aucun amendement n'est ouvert par cet audit.
