# Command Identity

- `propertyId` porte l'Aggregate destinataire.
- `addressIntentId` identifie une intention Address distincte et stable.
- `commandId` identifie une tentative logique de promotion et peut changer après correction/relecture.
- `expectedAuthoringVersion` protège le snapshot mais n'entre pas dans AddressId.

Property Authoring crée `addressIntentId` côté serveur lorsque des faits Address sont établis pour la première fois. Il le conserve lors des sauvegardes sans modification physique de l'adresse et le renouvelle atomiquement lorsqu'un nouveau couple `GeographicPlaceId + AddressLine` est accepté.

Ainsi, une modification sans rapport avec l'adresse ne change pas AddressId ; un véritable changement d'adresse en produit un nouveau.
