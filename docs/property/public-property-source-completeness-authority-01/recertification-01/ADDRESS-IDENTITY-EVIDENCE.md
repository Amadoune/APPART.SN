# Address Identity Evidence

F4 émet `addressIntentId` côté serveur uniquement après une adresse complète. Il conserve l’intention pour le même couple physique et la renouvelle lors d’un changement de PlaceId ou d’AddressLine canonique.

F2 fournit `AddressIdentityIssuerV1` et son implémentation déterministe UUIDv5. PropertyId + AddressIntentId identiques produisent le même AddressId ; une nouvelle intention produit une identité distincte. Aucun AddressId client n’est accepté ou persisté dans Authoring.

Les régressions F2 sont PASS. Cette source n’est pas la divergence F5.
