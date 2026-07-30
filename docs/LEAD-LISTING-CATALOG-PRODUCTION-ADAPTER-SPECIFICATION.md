# ListingCatalog Production Adapter Specification

`MaterializedListingCatalog` implémente le port historique `ListingCatalog`.

Séquence unique :

1. appeler `LeadEligibilitySourceDataReader::current(ListingId)` ;
2. exiger un record `Found` ;
3. construire `ListingContactEvidence(record.listingDecision, record.revision)`.

Les quatre valeurs `ListingContactability` sont recopiées sans branche métier. `SourceAbsent` ne peut pas produire `Missing`, car aucune révision n'est disponible. `Corrupted` reste une erreur technique.
