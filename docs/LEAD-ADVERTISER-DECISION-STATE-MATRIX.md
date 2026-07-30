# Lead Advertiser Decision State Matrix

Propriétaire unique : **ContactsLeads**.

Priorité normative :

| Priorité | Faits certifiés | Décision `AdvertiserEligibility` |
|---:|---|---|
| 1 | Advertiser explicitement absent | `Missing` |
| 2 | Advertiser existant, réception de Leads suspendue | `Suspended` |
| 3 | Advertiser actif mais différent du destinataire normatif du Listing | `NotListingRecipient` |
| 4 | Advertiser actif et destinataire normatif | `EligibleRecipient` |

La matrice est fermée, exhaustive et mutuellement exclusive grâce à sa priorité. ContactsLeads applique cette décision avant matérialisation ; le futur adaptateur ne reçoit que le résultat final.

Une absence de donnée n'est pas une preuve de `Missing`. Les faits d'absence et de suspension doivent être explicitement fournis par une autorité certifiée au futur producteur.
