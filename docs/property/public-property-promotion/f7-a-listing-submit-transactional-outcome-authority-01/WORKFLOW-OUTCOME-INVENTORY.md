# Inventaire des outcomes Workflow existants

| Status existant | Origines observées | Transition | Classe |
|---|---|---:|---|
| `Applied` | transition Workflow et handoffs locaux appliqués | requise | succès |
| `AlreadyApplied` | checksum/transition identique déjà présente et handoffs convergés | requise | succès idempotent |
| `Denied` | décision `ListingPublicationWorkflow` refusée avec diagnostic | aucune | refus métier |
| `ConcurrencyConflict` | version ou état concurrent | aucune | conflit |
| `PersistenceFailure` | état absent/corrompu, store/outbox/queue indisponible, transition rejetée, exception interne | aucune | indisponibilité/échec persistance |

Le catalogue exhaustif est `ListingPublicationOrchestrationStatus`. Il ne contient pas de statut Divergent distinct à cette frontière ; les divergences persistantes existantes sont réduites en conflit ou échec de persistance selon le store concerné.

Avant le Workflow, `PublicFactHandoffResult` possède `Applied`, `AlreadyApplied`, `DivergentIntent`, `VersionConflict`, `MissingCandidate`, `DependencyUnavailable`. Seuls `Applied` et `AlreadyApplied` permettent de poursuivre ; la composition actuelle l’impose déjà par rollback interne.
