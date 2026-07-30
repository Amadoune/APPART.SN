# Historical Redirect Certification Result Matrix

| Preuve | Résultat certifié |
|---|---|
| projection Current avec tables historiques indisponibles | 200, ReadModel exact |
| qualification Historical + résolution Resolved réelles | 301, `Location` exact |
| qualification Current / Unknown | 404, diagnostic distinct |
| qualification Ambiguous / Corrupted | 503, diagnostic distinct |
| resolver NotFound / DestinationMissing | 404, diagnostic distinct |
| resolver Loop / Chain / Ambiguous / Corrupted | 503, aucun `Location` |
| qualifier PostgreSQL indisponible | 503, `qualification_unavailable` |
| resolver PostgreSQL indisponible | 503, `resolver_unavailable` |
| Runtime Health | `Healthy` |
| route ListingId | 404 sans redirection |

Tous les composants applicatifs et PostgreSQL sont résolus par Laravel. Les données de scénario sont uniquement des décisions persistées de test.
