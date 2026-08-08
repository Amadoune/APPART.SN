# HTTP Matrix

| Statut public | HTTP |
|---|---:|
| Available | 200 |
| Missing | 404 |
| Corrupted | 503 |
| DependencyUnavailable | 503 |

La matrice s'applique aux cinq endpoints. Chaque réponse contient exclusivement `status` et `observedAt`, avec `Cache-Control: no-store` et `X-Content-Type-Options: nosniff`.

Routes :

- `GET /api/security-compliance/secret-inventory`
- `GET /api/security-compliance/security-audit`
- `GET /api/security-compliance/incident`
- `GET /api/security-compliance/privacy-policy`
- `GET /api/security-compliance/compliance-control`
