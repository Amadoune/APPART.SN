# Phase 5.1 — Identity & Access Completion Roadmap

## Séquence normative

| Jalon | Objet | État |
|---|---|---|
| 5.1A | Discovery / Blueprint | GO CERTIFIÉ, FERMÉE |
| 5.1B | Contracts Foundation | GO CERTIFIÉ, FERMÉE |
| 5.1C | Persistence Foundation | GO CERTIFIÉ, FERMÉE |
| 5.1D | Profile Claims Seed & Authority Cutover | GO CERTIFIÉ, FERMÉE |
| 5.1E | Runtime Composition & Availability Policy | OUVERTE — GO PROPOSÉ |
| 5.1F | Runtime Orchestration / Atomic Operations | FERMÉ |
| 5.1G | Event Contracts, Transport, Routing & Delivery | FERMÉ |
| 5.1H | Outbox Owner & Atomic Event Integration | FERMÉ |
| 5.1I | HTTP Runtime & Security | FERMÉ |
| 5.1J | Final Certification & Freeze | FERMÉ |

Un jalon ne s'ouvre qu'après GO certifié du précédent.

## Contenu des jalons

### 5.1B — Contracts

Authentication, attempts/lockout, sessions, recovery, Profile/Claims,
ContactChange/Revision, Closure, availability et privacy-safe events.

### 5.1C — Persistence

Migrations additives, mappings, repositories/stores, rollback, idempotence,
concurrence PostgreSQL et aucun changement 041–043.

### 5.1D — Profile cutover

Seed Snapshot V1, reservation des claims, divergence/quarantine, cutover et
rollback sans double autorité.

### 5.1E/F — Runtime et atomicité

Provider propriétaire, availability Status+Closure, orchestration auth/session,
recovery/password/session invalidation, contact activation/claim swap et close
/session invalidation.

### 5.1G/H — Events et Outbox

Catalogues Profile/Closure/security strictement nécessaires, transport,
routing, consumers, owner Outbox distinct, retries/replay et transactions
atomiques.

### 5.1I — HTTP

Login/logout/session/recovery/Profile/Closure routes, validation, cookies,
rate limits, authorization, enumeration resistance et tests sécurité.

### 5.1J — Final

Consolidation des certifications, baseline complète, registre de gel et
proposition GO FINAL.

## Exclusion permanente de Phase 5.1

`A-5.1-IAM-ERASURE-01` n'est ni un sous-jalon ni une dépendance cachée. Aucun
fichier 5.1 ne peut anonymiser ou détruire Historical Account.

## Prochaine porte

5.1E est le seul jalon ouvert. `A-5.1-IAM-PERSISTENCE-BOUNDARY-01` et 5.1D
sont GO CERTIFIÉS et FERMÉS. 5.1F reste fermé.
