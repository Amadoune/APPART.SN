# Public Projection Runtime Certification — matrice finale

| Capacité | Résultat |
|---|---|
| Transaction Aggregate + Outbox commune | PASS |
| Rollback commun et absence d'écriture partielle | PASS |
| Worker et ports résolus par Laravel | PASS |
| Claim, lease, registre et acknowledgement | PASS |
| Listing → projection → HTTP 200 | PASS |
| Redelivery et `AlreadyApplied`/`AlreadyConsumed` | PASS |
| Une seule projection après redelivery | PASS |
| Property → 101 Listings sur deux pages | PASS |
| Media → Property → 101 Listings | PASS |
| Current Active uniquement | PASS |
| Historical, Tombstone et Candidate non servis | PASS |
| Canonical inconnue et Historical → 404 | PASS |
| Store indisponible ou projection corrompue → 503 | PASS |
| noindex et JSON-LD issus du Store | PASS |
| Runtime Health | Healthy |
| Verdict proposé | GO |
