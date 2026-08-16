# Address Creation Integration

Séquence cible :

1. Property Authoring complet fournit addressIntentId, AddressLine et GeographicPlaceId sélectionné.
2. PromoteAuthoredPropertyV1 vérifie owner et version.
3. AddressIdentityIssuerV1 calcule AddressId.
4. L'orchestrateur construit `Address(AddressId, GeographicPlaceId, AddressLine)`.
5. `RegisterProperty` revalide Geography et les invariants Property.
6. PropertyRegistry persiste l'Aggregate et l'Address.

HTTP ne fournit jamais AddressId. UX, Projection, Search et Public Listing ne le créent pas. L'Issuer ne devient owner ni des faits Address ni de leur validation.
