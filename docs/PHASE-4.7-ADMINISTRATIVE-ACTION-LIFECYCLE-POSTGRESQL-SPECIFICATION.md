# Phase 4.7B — PostgreSQL Specification

Table propriétaire :

```text
administration_audit.administrative_action_lifecycle_transitions
```

Colonnes :

- `action_id` ;
- `version` ;
- `entry_kind` : `enrollment` ou `transition` ;
- `previous_state` ;
- `current_state` ;
- `action` ;
- `entry_checksum` ;
- `source_checksum` ;
- `mirror_checksum`.

La clé primaire `(action_id, version)` garantit l'unicité. La version d'enrôlement est exactement la version historique et peut être zéro. Une transition utilise exclusivement `expectedHistoricalVersion + 1`.

PostgreSQL n'accepte que les quatre transitions certifiées. L'index `(action_id, version DESC)` fournit la lecture courante déterministe.
