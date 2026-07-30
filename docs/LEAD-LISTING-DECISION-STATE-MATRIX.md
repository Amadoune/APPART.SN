# Lead Listing Decision State Matrix

Propriétaire unique : **ListingLifecycle**.

| État Listing certifié | Décision `ListingContactability` |
|---|---|
| `Published` | `Contactable` |
| `Draft`, `Submitted`, `UnderReview`, `ChangesRequested`, `Suspended` | `NotPublished` |
| `Expired`, `Withdrawn`, `Rejected`, `Archived` | `Closed` |
| absence explicitement certifiée dans la génération | `Missing` |

Les quatre décisions sont exhaustives et mutuellement exclusives. L'absence de ligne dans un futur stockage n'est jamais assimilée à `Missing`.

La décision est produite par l'autorité ListingLifecycle au moment où le fait source devient effectif. Les futurs adaptateurs la lisent telle quelle et ne consultent ni Aggregate, ni Repository, ni historique.
