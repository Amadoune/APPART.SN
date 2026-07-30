# Historical Redirect HTTP Observability Matrix

Le message stable `historical_redirect_http_outcome` contient le champ structuré `diagnostic_code` issu de `HistoricalRedirectHttpDiagnosticCode`.

| Situation | Code typé |
|---|---|
| qualification Current | `qualification_current` |
| qualification inconnue | `qualification_unknown` |
| qualification ambiguë | `qualification_ambiguous` |
| qualification corrompue | `qualification_corrupted` |
| qualifier indisponible | `qualification_unavailable` |
| décision absente | `resolver_not_found` |
| destination absente | `destination_missing` |
| boucle | `loop_detected` |
| chaîne | `chain_detected` |
| résolution ambiguë | `resolver_ambiguous` |
| résolution corrompue | `resolver_corrupted` |
| resolver indisponible | `resolver_unavailable` |

Les exceptions PostgreSQL et détails d'intégrité ne sont jamais inclus dans la réponse HTTP.
