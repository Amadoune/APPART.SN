# Lead Eligibility Source Runtime Binding Matrix

| Contrat | Implémentation | Cycle de vie |
|---|---|---|
| `LeadEligibilitySourceDataReader` | alias de `PostgreSqlLeadEligibilityDecisionStore` | singleton partagé |
| `ListingCatalog` | alias de `MaterializedListingCatalog` | singleton paresseux |
| `AdvertiserCatalog` | alias de `MaterializedAdvertiserCatalog` | singleton paresseux |

Il existe un seul binding par port. Aucun Fake, Null Object ou fallback n'appartient au graphe.
