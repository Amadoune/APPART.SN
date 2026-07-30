# HTTP Runtime Result Matrix

| État durable / requête | Résultat HTTP |
|---|---|
| canonical Current dans génération Active | 200, ReadModel exact |
| canonical inconnue | 404 |
| canonical Historical | 404 |
| ListingId interne | 404 au niveau route |
| projection absente | 404 |
| Projection Store indisponible | 503 |
| payload ou checksum divergent | 503 fail-safe |
| projection noindex | 200, `noindex, follow`, aucun JSON-LD |
| projection indexable | 200, canonical/robots/JSON-LD copiés |

Aucun scénario ne déclenche une lecture Aggregate, un recalcul ou une reconstruction HTTP.
