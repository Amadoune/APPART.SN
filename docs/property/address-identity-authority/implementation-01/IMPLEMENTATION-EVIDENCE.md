# F2 — Implementation Evidence

## Primitives

- `AddressIntentId` : immutable, validé, UUID canonique ;
- `AddressIdentityIssuerV1` : contrat applicatif owner RealEstateCatalog ;
- `AddressIdentityIssuanceResult` et `AddressIdentityIssuanceStatus` : résultat fermé ;
- `DeterministicAddressIdentityIssuerV1` : implémentation pure UUIDv5 ;
- `AddressIdentityServiceProvider` : binding réel de l’interface.

## Déterminisme démontré

Le vecteur figé suivant protège le namespace et la canonicalisation :

- PropertyId : `10000000-0000-4000-8000-000000000001` ;
- AddressIntentId : `20000000-0000-4000-8000-000000000001` ;
- AddressId attendu : `fe428218-c951-58ce-9bac-098eba707ef9`.

Les tests démontrent la stabilité entre appels, instances et reconstructions des Value Objects. Une autre intention ou un autre Property produit une identité distincte. Le résultat est accepté sans modification par le Value Object Domain `AddressId`.

## Pureté démontrée

Les tests d’architecture interdisent Persistence, SQL, Repository, Ledger, Clock, random, Geography, Property Authoring, Projection, Search, HTTP et dépendance framework dans l’autorité.

PostgreSQL : **NOT_APPLICABLE**. Aucune migration n’a été créée.
