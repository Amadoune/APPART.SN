# HTTP Matrix — Legacy Migration

| Endpoint | Statuts HTTP 200 | HTTP 404 | HTTP 503 |
|---|---|---|---|
| `GET /api/legacy-migration/inventory` | Available | Missing | Corrupted, DependencyUnavailable |
| `GET /api/legacy-migration/wave` | Ready, Blocked, Completed | Missing | Corrupted, DependencyUnavailable |
| `GET /api/legacy-migration/reconciliation` | Matched, Divergent, Pending | Missing | Corrupted, DependencyUnavailable |
| `GET /api/legacy-migration/quarantine` | Empty, ContainsItems | Missing | Corrupted, DependencyUnavailable |
| `GET /api/legacy-migration/cutover` | Ready, Blocked, Completed | Missing | Corrupted, DependencyUnavailable |

Les mappings couvrent exhaustivement les 27 états certifiés. Aucun fallback ou mapping par défaut n'est utilisé.

Chaque Request exige `subjectKey` et `observedAt` au format UTC canonique accepté, et refuse tout champ de query inconnu avec HTTP 422.
