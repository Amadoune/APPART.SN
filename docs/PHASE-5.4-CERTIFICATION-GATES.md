# Phase 5.4 — Certification Gates

## 1. Règle générale

Chaque voie 5.4A, 5.4B et 5.4C franchit ses gates indépendamment. Une gate
fermée dans une voie n'autorise pas la suivante dans une autre. Chaque
ouverture exige un prononcé d'autorité explicite.

## 2. Gates minimales

| Gate | Objectif et livrables | Critères GO | Critères NO GO | Campagnes attendues |
|---|---|---|---|---|
| Discovery / Blueprint | besoin, ownership, frontières, dépendances, risques, exclusions | aucune ambiguïté non classée ; aucun code | owner inconnu, dépendance implicite, document incomplet | cohérence documentaire, recherche exhaustive, diff check |
| Boundary Audits | qualifier chaque frontière externe | frontière certifiée, fonction retirée ou amendement identifié | dépendance ambiguë ou accès interne requis | documentation et Architecture si registres testés |
| Contracts Foundation | Commands, Queries, résultats, ports et catalogue Event documentaire fermés | versionnés, exhaustifs, framework-agnostic | booléens ouverts, exceptions techniques, dépendance Laravel/PDO | Unit contrat, Architecture, PHPStan, Pint |
| Persistence Foundation | stores, mappers, migrations owner-locales | additive, rollback, idempotence, concurrence, aucune FK externe | scan non borné, transaction externe, rollback incomplet | Unit, Architecture, PostgreSQL ciblé/complet, PHPStan, Pint |
| Runtime Foundation | composition owner-scoped et availability | singleton/lazy, fail-closed, diagnostics sûrs | binding ambigu, fallback, dépendance interne externe | Unit, Runtime, Architecture, PostgreSQL ciblé |
| HTTP Foundation | adaptation Commands/Queries, validation et sécurité | auto-scope, JSON fermé, anti-énumération, no-store | logique métier, accès store/SQL, diagnostic public | Unit mapper, Feature, Runtime, Architecture, PostgreSQL ciblé |
| Event / Delivery / Outbox | events propriétaires, transport, routing, append atomique | catalogue fermé, idempotence, retry/replay/quarantaine, atomicité locale | route dynamique, payload sensible, handoff non certifié | Unit, Architecture, Delivery, PostgreSQL ciblé/complet, concurrence |
| Operational Certification | reprise, observabilité, rétention, sécurité et audit | preuves terminales, diagnostics sans PII, procédures de reprise | preuve non terminale ou dépendance non certifiée | Feature, Runtime, Architecture, PostgreSQL complet, PHPStan, Pint |
| Final Certification & Freeze | consolidation des voies certifiées et baseline Git | toutes gates fermées, aucun amendement ouvert, worktree et preuves propres | réserve bloquante, inventaire incomplet, documentation incohérente | suites complètes, diff check, inventaire/scan/manifeste Git |

## 3. Critères particuliers par voie

### 5.4A — Lead Ingress

- preuve de consentement et politique de rétention ;
- contactabilité et destinataire résolus par frontières certifiées ;
- anti-abus, confidentialité, retry et quarantaine ;
- aucun contact ni identifiant personnel dans logs ou Events non autorisés.

### 5.4B — Reservation Intake

- autorité de disponibilité certifiée ;
- conflit, optimistic locking et concurrence démontrés ;
- aucune double réservation ;
- handoff vers lifecycle existant sans nouvelle transition implicite.

### 5.4C — Favorites

- ownership Favorites explicitement certifié ;
- collection strictement privée et auto-scopée ;
- pagination déterministe ;
- ajout/retrait idempotents et politique de fermeture Account certifiée.

## 4. Recertification

Une Foundation rejoue au minimum ses campagnes ciblées, Architecture complète,
PHPStan, Pint et `git diff --check`. PostgreSQL complet est obligatoire pour
toute modification de persistence, migration, transaction, concurrence,
delivery ou outbox. Les campagnes globales peuvent être qualifiées uniquement
par une décision d'autorité explicite ; un timeout n'est jamais un PASS.

Toute modification d'une capacité gelée exige :

1. un amendement versionné ;
2. une analyse d'impact ;
3. les campagnes ciblées de la capacité modifiée ;
4. les non-régressions globales déterminées par l'autorité.

## 5. Gate actuelle

Seul le Discovery / Blueprint est ouvert. Boundary Audits, Contracts,
Persistence, Runtime, HTTP, Event/Delivery/Outbox, Operational Certification
et Final Freeze restent `NON OUVERTS`.

