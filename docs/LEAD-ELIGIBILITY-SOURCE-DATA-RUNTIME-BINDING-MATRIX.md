# Lead Eligibility Source Data Runtime Binding Matrix

| Composant | Binding | Cycle de vie |
|---|---|---|
| `LeadEligibilitySourceDataMapper` | classe elle-même | singleton paresseux |
| `PostgreSqlLeadEligibilityDecisionStore` | classe elle-même | singleton paresseux |
| `LeadEligibilityDecisionMaterializer` | alias du store PostgreSQL | instance partagée |
| `PDO` | PostgreSQL Runtime existant | réutilisé |

`LeadEligibilitySourceDataReader`, `ListingCatalog` et `AdvertiserCatalog` ne sont pas bindés pendant ce sprint. Aucun appel, lecture ou transaction n'est exécuté au bootstrap.
