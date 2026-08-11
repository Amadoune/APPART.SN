# Transport Matrix

| Option | Identification déterministe | Owner explicite | Lookup supplémentaire | Concurrence multi-session | Décision |
|---|---:|---:|---:|---:|---|
| A — AccountId | NON | OUI | ambigu | incompatible | REJETÉE |
| B — SessionId | OUI | NON | owner à redériver | techniquement possible | REJETÉE |
| C — AccountId + SessionId | OUI | OUI | row Session uniquement | compatible | RETENUE |
| D — handle opaque distinct | OUI | possible | nouveau mapping requis | possible | REJETÉE, abstraction sans besoin |

## Champs explicitement exclus

| Champ | Motif d'exclusion |
|---|---|
| secret/cookie | preuve sensible consommée pendant l'inspection |
| secret_hash/HMAC | persistence sensible |
| session state | doit être relu autoritativement |
| version | doit être relue pour l'optimistic locking |
| timestamps/deadlines | doivent être relus puis évalués par F1 |
| policy version | propriété du snapshot, non du transport |
| rôles/permissions | hors identité Session et susceptibles d'évoluer |

## Réduction future

`AuthenticatedSessionContext` ne décide rien. Il permet seulement : lookup `SessionId` → contrôle owner `AccountId` → réduction F1 → mutation atomique éventuelle.
