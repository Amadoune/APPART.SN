# F2 — Address Identity Foundation Implementation 01

## Statut

Implémentation ouverte conformément au Blueprint Address Identity Authority 01 et à la fermeture GO de F1 Geography Selection.

## Autorité matérialisée

`AddressIdentityIssuerV1` appartient à `RealEstateCatalog Application`. Son unique responsabilité est la fonction pure :

`(PropertyId, AddressIntentId) → AddressId`

`AddressIntentId` est un Value Object immutable acceptant exclusivement un UUID canonique. F2 fournit sa reconstruction validée mais ne décide pas de sa création ou de sa rotation dans Property Authoring.

## Stratégie d’identité

- algorithme : UUIDv5 RFC 4122 ;
- namespace : `6ba7b811-9dad-11d1-80b4-00c04fd430c8` ;
- nom UTF-8 : `https://appart.sn/real-estate-catalog/address-intents/v1/{propertyId}/{addressIntentId}` ;
- PropertyId et AddressIntentId sont normalisés par leurs Value Objects en UUID minuscules canoniques.

L’Issuer ne lit ni clock, ni configuration métier, ni base, ni autre module. Il n’utilise aucun aléa et ne possède aucun ledger.

## Résultat fermé

La frontière pure retourne `AddressIdentityIssuanceResult` avec le statut `Issued` et un `AddressId` Domain validé. `Collision` n’est pas émis artificiellement : aucune intégration Registry/Aggregate n’existe dans F2 pour démontrer une collision réelle. Cette qualification reste réservée à la future composition Promotion/Registry.

## Composition

Le binding nominatif relie `AddressIdentityIssuerV1` à `DeterministicAddressIdentityIssuerV1` comme singleton sans dépendance Infrastructure.

## Hors périmètre préservé

Aucune persistance `addressIntentId`, rotation Authoring, promotion, composition `RegisterProperty`, route HTTP, migration ou modification de `Address`, `AddressId` et `ChangeAddress`.
