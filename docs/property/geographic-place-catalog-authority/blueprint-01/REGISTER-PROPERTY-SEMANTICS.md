# RegisterProperty Semantics

## Décision actuelle

Lorsque `Address` est absent, `RegisterProperty` ne consulte pas Geography. Lorsqu’il est présent, le use case appelle exactement une fois `statusOf(address.placeId)`.

| Résultat | Décision actuelle |
|---|---|
| `Usable` | poursuit vers `Property::register`, puis `PropertyRegistry::add` |
| `NotFound` | `UnavailableGeographicPlace` |
| `Disabled` | `UnavailableGeographicPlace` |
| `Merged` | `UnavailableGeographicPlace` |
| `NotAddressable` | `UnavailableGeographicPlace` |

Missing est donc déjà représenté par `NotFound`. Disabled et merged sont refusés sans substitution d’identité.

## Erreurs de dépendance

Une exception de lecture n’est pas interceptée. L’exécution s’arrête avant la construction et l’ajout de Property. Cette propagation est fail-closed, mais le contrat ne fournit pas encore une réduction fermée distinguant corruption et indisponibilité.

## Invariants inchangés

Le catalogue ne décide pas des invariants Property. `PropertyTypePolicy`, `Property::register` et `PropertyRegistry` restent propriétaires de leurs décisions. Aucun statut autre que `Usable` ne peut être traité comme un succès.
