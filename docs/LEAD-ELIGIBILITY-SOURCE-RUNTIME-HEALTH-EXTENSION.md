# Lead Eligibility Source Runtime Health Extension

Runtime Health est explicitement étendu avec :

- `ListingCatalog` ;
- `AdvertiserCatalog`.

Le nombre de capacités passe de 30 à **32**. L'inspection vérifie uniquement binding, compatibilité et constructibilité. Elle n'appelle ni `contactabilityOf()`, ni `eligibilityFor()`, ne lit aucune donnée et ne construit aucune évidence ou `LeadEligibilityProof`.
