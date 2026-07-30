# Lead Eligibility Contract Compatibility Matrix

| Élément historique | Décision | 4.4C-S1 | 4.4D |
|---|---|---|---|
| `ListingCatalog` | conservé | adaptateur + binding | lecture de l'évidence |
| `AdvertiserCatalog` | conservé | adaptateur + binding | lecture de l'évidence |
| `ListingContactEvidence` | conservé | résultat immuable | entrée de la preuve combinée |
| `AdvertiserEligibilityEvidence` | conservé | résultat immuable | entrée de la preuve combinée |
| `LeadEligibilityProof` | conservé | aucune construction | construction via `fromEvidence()` |
| `CreateLead` | inchangé | aucune modification | coexistence/migration à traiter séparément si nécessaire |
| Aggregate `Lead` | inchangé | aucune modification | reçoit toujours le même type de preuve |

## Types retirés du blueprint

`ListingEligibilityProof` et `AdvertiserEligibilityProof` ne seront pas introduits. Un nouveau `LeadEligibilityProof` ne sera pas introduit. Cette suppression élimine toute coexistence ambiguë sans rupture de compatibilité.
