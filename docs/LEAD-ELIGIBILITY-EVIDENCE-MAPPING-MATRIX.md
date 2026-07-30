# Lead Eligibility Evidence Mapping Matrix

| Donnée matérialisée | Évidence produite | Transformation |
|---|---|---|
| `record.listingDecision` | `ListingContactEvidence.state` | aucune |
| `record.advertiserDecision` | `AdvertiserEligibilityEvidence.state` | aucune |
| `record.revision` | révision des deux évidences | même instance |
| `SourceAbsent` | aucune | erreur d'infrastructure |
| `Corrupted` | aucune | erreur d'infrastructure |
| Advertiser non couvert | aucune | erreur d'intégrité de source |

Une décision matérialisée `Missing` est recopiée comme `Missing`. Une absence physique n'est jamais assimilée à cette décision.
