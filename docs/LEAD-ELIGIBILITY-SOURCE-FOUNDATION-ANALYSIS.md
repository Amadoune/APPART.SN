# Lead Eligibility Source Foundation Analysis

## Décision

Deux adaptateurs mécaniques implémentent les ports historiques à partir du seul `LeadEligibilitySourceDataReader` :

```text
ListingCatalog ─────► MaterializedListingCatalog ────┐
                                                      ├─► LeadEligibilitySourceDataReader
AdvertiserCatalog ─► MaterializedAdvertiserCatalog ─┘
```

Un record `Found` est recopié dans l'évidence historique correspondante. `SourceAbsent`, `Corrupted` et une identité Advertiser non couverte sont des erreurs d'infrastructure explicites. Ils ne deviennent jamais des décisions Domain.

Les adaptateurs ne construisent pas `LeadEligibilityProof` et n'accèdent ni au materializer, ni à PostgreSQL directement.
