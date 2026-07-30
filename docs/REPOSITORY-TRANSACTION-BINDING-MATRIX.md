# Repository/Transaction Binding Matrix

| Producteur | Événement Public Projection | Transaction Repository | Binding Runtime | Décision |
|---|---|---|---|---|
| Listing | `listing.reconstruction.requested` | `ListingTransaction` | Participant Aggregate/Outbox | Composé |
| Property | `property.reconstruction.requested` | `PropertyTransaction` | Participant Aggregate/Outbox | Composé |
| Media | `media.reconstruction.requested` | `MediaCollectionTransaction` | Participant Aggregate/Outbox | Composé |
| Search | `search.reconstruction.requested` | Writer avec participation PDO native | Aucun participant Repository | Non concerné |
| Content/SEO | `content_seo.reconstruction.requested` | Writer avec participation PDO native | Aucun participant Repository | Non concerné |

Listing, Property, Media, la transaction propriétaire, le participant et les adaptateurs Outbox partagent la PDO Laravel `pgsql`.
