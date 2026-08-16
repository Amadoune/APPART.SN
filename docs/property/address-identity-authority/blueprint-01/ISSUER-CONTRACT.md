# Issuer Contract

## AddressIdentityIssuerV1

Entrée immuable :

- `propertyId` canonique ;
- `addressIntentId` canonique, émis côté serveur par Property Authoring.

Sortie : `AddressIdentityIssuanceResult` contenant statut fermé et `AddressId` uniquement pour `Issued`.

`commandId` de promotion et version Authoring ne participent pas à l'identité : ils contrôlent l'orchestration, tandis que plusieurs retries/commandes de la même intention Address doivent produire le même ID. Aucun fait Address n'est requis.

Les entrées structurellement invalides sont rejetées à la construction des Value Objects avant l'Issuer.
