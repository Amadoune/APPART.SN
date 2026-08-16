# Séparation Promotion / Listing

La frontière certifiée reste :

1. transaction Promotion RealEstateCatalog ;
2. commit Property + ledger F6 ;
3. transaction Listing distincte ;
4. commit ou rollback de la tentative Listing selon son outcome explicite.

Une défaillance Listing ne compense jamais la Property. Ce comportement est volontaire : Property n’est pas publication publique et demeure une précondition Domain réutilisable.

La future correction ne modifie ni `PromoteAuthoredPropertyV1`, ni le ledger 100, ni la transaction Property, ni `RegisterProperty`.
