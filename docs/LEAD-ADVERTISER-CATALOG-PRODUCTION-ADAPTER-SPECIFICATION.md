# AdvertiserCatalog Production Adapter Specification

`MaterializedAdvertiserCatalog` implémente le port historique `AdvertiserCatalog`.

Séquence unique :

1. appeler `LeadEligibilitySourceDataReader::current(ListingId)` ;
2. exiger un record `Found` couvrant exactement l'Advertiser demandé ;
3. construire `AdvertiserEligibilityEvidence(record.advertiserDecision, record.revision)`.

La vérification d'identité protège l'intégrité du record ; elle ne produit aucune décision. Les quatre valeurs `AdvertiserEligibility` sont recopiées sans classification.
