# APPART.TEST LISTING PUBLICATION REGISTRY SYNCHRONIZATION 01 — Transition Consistency Matrix

| Transition | État workflow | État Aggregate attendu | Writer actuel | Écart |
|---|---|---|---|---|
| initialisation | Draft | Draft | workflow store / création Listing séparés | aucune composition atomique explicite |
| Submit | Submitted | Submitted | `ListingPublicationWorkflowStore` | `ListingRegistry` non modifié |
| BeginReview | UnderReview | UnderReview | `ListingPublicationWorkflowStore` | `ListingRegistry` non modifié |
| ApproveAndPublish | Published | Published | `ListingPublicationWorkflowStore` | `ListingRegistry` non modifié ; média, expiration et evidence absents |

## Preuve existante

`PublicProjectionEndToEndRuntimeCertificationTest` construit directement un Listing publié puis l'ajoute au Registry. Cette fixture démontre la projection depuis un état terminal, mais ne démontre pas la synchronisation du workflow ; elle ne peut pas être réutilisée comme correction.
