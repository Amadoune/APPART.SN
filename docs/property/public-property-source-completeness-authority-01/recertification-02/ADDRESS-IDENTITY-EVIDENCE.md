# Address Identity Evidence

F2 consomme exclusivement `PropertyId` et `AddressIntentId` serveur :

`AddressIdentityIssuerV1::issue(PropertyId, AddressIntentId)` → `AddressIdentityIssuanceResult::Issued` → `AddressId`.

L’implémentation productive utilise un namespace et un nom versionnés pour calculer un UUIDv5. Elle n’utilise ni random, ni valeur AddressId client, ni persistence supplémentaire.

La preuve d’assemblage appelle directement `DeterministicAddressIdentityIssuerV1` deux fois avec les mêmes entrées et obtient le même AddressId. Les tests F2 Unit, binding et Architecture sont PASS.
