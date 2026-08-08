# Notifications Outbox Matrix

| Situation | Résultat |
|---|---|
| Nouveau message | Applied |
| Même message et checksum | AlreadyApplied |
| Même message, contenu ou checksum divergent | DivergentMessage |
| PostgreSQL indisponible à l'append | DependencyUnavailable |
| Retry 0 à 9 | Éligible à `pending` |
| Retry 10 | Exclu de `pending` |

La lecture est ordonnée par `created_at`, puis `message_id`, avec une limite de 1 à 100.
