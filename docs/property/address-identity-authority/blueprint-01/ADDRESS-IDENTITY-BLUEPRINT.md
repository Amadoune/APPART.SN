# Address Identity Authority — Blueprint 01

## Décision V1

RealEstateCatalog Application possède `AddressIdentityIssuerV1`, une autorité pure émettant un UUIDv5 déterministe depuis `propertyId` et une identité serveur stable `addressIntentId`.

L'émission intervient dans `PromoteAuthoredPropertyV1`, après lecture et validation de la version Authoring, avant la construction de `Address` et l'appel à `RegisterProperty`.

Même intention : même AddressId. Nouvelle intention Address : nouvel addressIntentId et donc nouvelle identité. Aucun ID client, clock, aléa, Projection ou ledger n'intervient.

**GO PROPOSÉ.**
