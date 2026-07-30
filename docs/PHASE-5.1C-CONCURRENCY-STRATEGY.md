# Phase 5.1C — PostgreSQL Concurrency Strategy

## 1. Mécanismes

- optimistic locking `WHERE version = :expected_version` ;
- contrainte unique comme arbitre final d'identité ;
- insert idempotent par `intent_id`/checksum owner ;
- `SELECT ... FOR UPDATE` seulement sur le root owner ;
- advisory lock uniquement sur une clé owner documentée si aucun root n'existe
  encore ;
- isolation `READ COMMITTED` suffisante avec contraintes et locks explicites ;
- rollback de toute transaction sur exception inattendue.

## 2. Scénarios obligatoires

| Owner | Course | Verdict attendu |
|---|---|---|
| Attempts | deux failures même intent | compteur +1 une seule fois |
| Attempts | failures distincts atteignent seuil | lockout unique/cohérent |
| Sessions | deux renew même version | un Renewed, un VersionConflict/AlreadyApplied |
| Sessions | create vs invalidate-all | aucune session utilisable avant checkpoint |
| Recovery | deux consume | un seul Password-change admissible |
| Recovery | expire vs consume | un seul état terminal |
| Profile | deux ChangeName | un seul expectedVersion gagne |
| Claims | deux Accounts réservent même claim | un seul Reserved |
| Claims | même Account même intent | convergence AlreadyApplied |
| Contact | verify vs expire | un seul état durable |
| Contact | activate vs cancel | un seul gagnant |
| Revisions | même result version | une seule revision |
| Closure | confirm vs cancel | un seul état |
| Closure | close vs reopen | ordre/version déterministes |

## 3. Identity Claims

La preuve ultime est une contrainte unique sur `(claim_type,
claim_fingerprint)`, couvrant Active, Reserved et Superseded. Aucun
check applicatif préalable n'est considéré comme suffisant.

Le résultat `ClaimConflict` ne révèle ni owner, ni existence publique.

## 4. Session invalidation

`session_invalidation_checkpoints` porte un compteur monotone par Account.
Create/Renew copie le checkpoint observé. InvalidateAll incrémente ce compteur
avec expected version. Une inspection compare toujours le issued checkpoint au
checkpoint courant.

Cette stratégie rend inoffensive une session insérée avant le commit
d'invalidation, sans UPDATE massif et sans transaction cross-owner.

## 5. Rollback

Tests fault-injection obligatoires après chaque write intermédiaire d'une
transaction locale :

- aucune version incrémentée partiellement ;
- aucun intent réservé sans état ;
- aucune claim orpheline ;
- aucun challenge consommé sans résultat durable ;
- aucune revision sans Profile correspondant lorsque l'opération atomique sera
  composée en 5.1F.

Les quatre derniers multi-owner sont spécifiés ici mais leur preuve atomique
finale appartient à 5.1F.

## 6. Tests PostgreSQL 18.x

Chaque scénario utilise deux connexions/processus réels, barrière de départ,
timeout borné et inspection finale indépendante. SQLite, mocks PDO et
transactions séquentielles ne certifient pas la concurrence.

## 7. Deadlocks/retry

Ordre de locks futur :

```text
Account logical key
→ Claim fingerprint
→ ContactChange
→ Profile
→ Revision
→ Session checkpoint
→ Closure
```

Une détection PostgreSQL `40P01` est classée retryable avec intent identique et
backoff borné. Un retry ne change jamais checksum/occurredAt.
