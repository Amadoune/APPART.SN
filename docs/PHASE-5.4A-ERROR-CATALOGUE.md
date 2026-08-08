# Phase 5.4A — Public Error Catalogue

## Résultats de soumission

| Résultat | Terminal | LeadIngressId |
|---|---:|---:|
| `Accepted` | Oui | Oui |
| `AlreadyAccepted` | Oui | Oui |
| `Rejected` | Oui | Non |
| `DivergentIntent` | Oui | Non |
| `NotContactable` | Oui | Non |
| `ContactPrincipalUnavailable` | Oui | Non |
| `RecipientNotEligible` | Oui | Non |
| `Corrupted` | Oui | Non |
| `DependencyUnavailable` | Non | Non |

## Résultats de lecture

| Résultat | Receipt |
|---|---:|
| `Found` | Oui |
| `Missing` | Non |
| `Forbidden` | Non |
| `Corrupted` | Non |
| `DependencyUnavailable` | Non |

## Erreurs publiques

Le catalogue public fermé contient :

- `InvalidRequest` ;
- `NotContactable` ;
- `RecipientUnavailable` ;
- `Conflict` ;
- `NotFound` ;
- `Forbidden` ;
- `DependencyUnavailable` ;
- `InternalFailure`.

Aucune exception technique, diagnostic, SQL state ou détail owner ne peut être
exposé.
