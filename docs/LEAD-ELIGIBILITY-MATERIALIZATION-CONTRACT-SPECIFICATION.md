# Lead Eligibility Materialization Contract Specification

Le port `LeadEligibilityDecisionMaterializer` reçoit un `LeadEligibilityMaterialization` immuable contenant :

- `ListingId` ;
- destinataire normatif optionnel `AdvertiserId` ;
- `ListingContactability` ;
- révision Listing ;
- Advertiser évalué ;
- `AdvertiserEligibility` ;
- révision Advertiser.

Les deux révisions doivent être identiques et UTC. Le port persiste uniquement des décisions déjà produites. Il ne construit ni évidence historique, ni `LeadEligibilityProof`.

`LeadEligibilitySourceDataReader` expose `current()` et `history()` sous forme de records applicatifs immuables. `SourceAbsent` signifie absence de matérialisation et ne signifie jamais `Missing`.
