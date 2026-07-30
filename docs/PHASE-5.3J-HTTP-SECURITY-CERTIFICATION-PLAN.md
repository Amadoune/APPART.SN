# Phase 5.3J — HTTP & Security Certification Plan

## Gate préalable

Avant toute implémentation HTTP :

`A-5.3-MODERATION-HTTP-READ-BOUNDARIES-01` doit être explicitement ouvert puis
GO CERTIFIÉ et FERMÉ.

Il devra rendre exécutables les quatre Queries V1 sans modifier les stores,
migrations, Runtime Health ou contrats métier gelés.

## Séquence autorisable après levée du gate

1. implémentations owner-scoped et bindings des Queries ;
2. audit de résolution Laravel des Commands, Queries et IAM ;
3. HTTP Runtime owner-scoped sans logique métier ;
4. Request validation stricte et mappers fermés ;
5. Controllers minces ;
6. routes sous session IAM, CSRF et rate limiting HMAC ;
7. tests et représentation 5.3J.

Chaque étape exige une autorisation d'autorité conforme à la gouvernance ; ce
plan ne les ouvre pas.

## Campagnes de certification

### Unit

- chaque résultat Command et Query mappé ;
- chaque décision IAM fail-closed ;
- refus des champs inconnus ;
- aucun actorAccountId client ;
- ETag/version uniquement selon contrat.

### Feature HTTP

- session obligatoire ;
- auto-scope ;
- CSRF sur toutes les mutations ;
- Idempotency-Key obligatoire ;
- anti-énumération ;
- rate limiting sans PII ;
- headers `no-store` et `nosniff` ;
- absence de diagnostics internes ;
- quatre yeux préservés.

### Architecture

- HTTP dépend uniquement des contrats Application et IAM publics ;
- aucun import Domain Aggregate, Persistence, Infrastructure, PDO ou SQL ;
- aucun accès Event, Delivery, Outbox ou handoff Listing ;
- Controllers sans décision métier ;
- aucune modification des capacités gelées.

### PostgreSQL

- parcours soumission/lecture propre ;
- queue read/claim ;
- dossier, validation, constat, décision, supersession et clôture ;
- idempotence, rollback, savepoint et concurrence ;
- aucune modification des migrations 063–069.

### Qualité

- Architecture ciblée et complète ;
- Unit et Feature ciblés ;
- PostgreSQL ciblé puis complet ;
- PHPStan zéro erreur ;
- Pint PASS ;
- `git diff --check` PASS.

## Critères GO futurs

- toutes les Queries et Commands sont exécutables via leurs frontières ;
- aucune lecture directe des stores par HTTP ;
- IAM est fail-closed pour toute opération privée ;
- les mappers sont exhaustifs ;
- aucune PII, secret ou diagnostic interne n'est exposé ;
- aucune capacité gelée n'est modifiée ;
- toutes les campagnes terminales sont vertes.

## Décision actuelle

**NO GO PROPOSÉ** pour l'implémentation HTTP 5.3J. Le manque est structurel,
unique et circonscrit aux Queries owner-scoped absentes.
