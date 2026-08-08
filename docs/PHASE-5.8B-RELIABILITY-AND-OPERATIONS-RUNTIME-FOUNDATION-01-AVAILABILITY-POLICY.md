# Phase 5.8B — Reliability & Operations — Availability Policy

| ReadStatus | Runtime Availability |
|---|---|
| Found | Available |
| Missing | Available |
| Corrupted | Corrupted |
| DependencyUnavailable | DependencyUnavailable |
| exception | DependencyUnavailable |

La réduction est mécanique, exhaustive et sans fallback. `DependencyUnavailable` prévaut sur `Corrupted` lors de l'inspection des sept streams. Aucun statut n'est interprété comme une décision métier.
