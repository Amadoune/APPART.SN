# Failure Modes

- Listing missing → `ListingMissing` ;
- Listing non Published → `ListingNotPublished` ;
- ListingId invalide/non canonical → `InvalidListingIdentity` ;
- collision store → `CanonicalCollision`/`Divergent` ;
- rejet par `CanonicalPolicy` → `CanonicalRejected` ;
- dépendance owner indisponible → `DependencyUnavailable`.

Title, Geography et PropertyType manquants n’empêchent pas le calcul du path, puisqu’ils n’en sont pas des sources. Toutes les erreurs restent fail-closed.
