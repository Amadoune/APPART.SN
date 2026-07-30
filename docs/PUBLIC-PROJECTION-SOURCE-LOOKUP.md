# Lookup concret Public Projection

| Message | Résolution |
|---|---|
| Listing, Search, Content/SEO prêt | `Resolved(listingId)` |
| Property valide | `MultiTargetResolved(Property, propertyId)` |
| Media trouvée | `MultiTargetResolved(Media, mediaCollectionId, propertyId)` |
| Media absente | `MediaOwnershipMissing` |
| identité invalide | `InvalidIdentity` |
| source durable corrompue ou divergente | `Corrupted` |

Les requêtes Property et Media ne préchargent aucun Listing. Elles sont directement consommables
par le Consumer 3.9C et la stratégie paginée ADR-1010.
