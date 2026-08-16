# Preuve d’intégration Submit

| Verdict Promotion | Réduction Submit | Listing |
|---|---|---|
| `Applied` | poursuite | `Submitted` si la transition Listing réussit |
| `AlreadyApplied` par ledger | poursuite | `Submitted` |
| `AlreadyApplied` par Property canonique ledgerless | poursuite | `Submitted` |
| `DivergentCommand` | `DivergentIntent` | Draft / non Submitted |
| `DomainRejected` | `LifecycleConflict` | non Submitted |
| `DependencyUnavailable` | `DependencyUnavailable` | non Submitted |
| `VersionConflict` | `ConcurrentModification` | non Submitted |
| `OwnershipMismatch` | `NotFoundOrForbidden` | non Submitted |
| `IncompleteAuthoring` | `Incomplete` | non Submitted |

Le code Submit n’a pas été modifié par F7-B ni par cette recertification.
