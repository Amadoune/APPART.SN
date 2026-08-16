# Failure Modes

| Cas | Résultat | Mutation | Retry / compensation |
|---|---|---|---|
| Authoring absent | `AuthoringMissing` | Non | Retry après création réelle |
| Owner divergent | `OwnershipMismatch` | Non | Non avec la même identité |
| Version divergente | `VersionConflict` | Non | Nouvelle commande après relecture |
| Données incomplètes | `IncompleteAuthoring` | Non | Après complétion Authoring |
| Aggregate existant compatible | `AlreadyApplied` | Non | Submit peut continuer |
| Aggregate existant divergent | `DivergentCommand` | Non | Pas de fallback ; intervention explicite |
| Validation Domain / place | `DomainRejected` | Non, rollback | Après correction de la source et nouvelle commande |
| Persistance indisponible | `DependencyUnavailable` | Non/rollback | Même commande |
| Event/outbox indisponible dans transaction | `DependencyUnavailable` | Non/rollback | Même commande |
| Réponse perdue après commit | `AlreadyApplied` au replay | Non au replay | Même commande |
| Replay checksum divergent | `DivergentCommand` | Non | Jamais automatiquement |

Aucune compensation distribuée. Un Property committé suivi d'un échec Listing demeure valide mais non public ; le replay Submit le reconnaît.
