# F4 — Property Authoring Source Completeness Implementation 01

## Réouverture

Le NO GO initial était causé exclusivement par l’absence d’une acquisition Geography autoritative. F4-A Authority et F4-A Implementation ont depuis matérialisé la sélection F1, son contexte de preuve et `GeographySelectionReplayValidatorV1`. Cette divergence est levée ; F4 est réouverte sans modifier F0–F4-A.

## Snapshot Authoring

`PropertyAuthoringState` conserve son identité, owner, version, intention, checksum, type et labels legacy. Il porte désormais huit faits supplémentaires nullable pour préserver les snapshots historiques :

- `propertyReference` ;
- `surfaceSquareMeters` ;
- `rooms` ;
- `bathrooms` ;
- `constructionYear` ;
- `geographicPlaceId` ;
- `addressLine` ;
- `addressIntentId`.

Les anciens snapshots se reconstruisent avec `null` et sont mécaniquement `IncompleteForPromotion`.

## Acquisition et validation

Les faits owner-authored passent par les Requests Authoring existantes. L’owner reste issu de la session IAM. `addressIntentId`, AddressId, BusinessYear et ownerAccountId ne sont jamais acceptés comme autorités client.

Lorsqu’un `geographicPlaceId` est soumis, son type, parent, curseur d’entrée et limite sont obligatoirement transmis à l’enrichisseur. Celui-ci rejoue F1 via `GeographySelectionReplayValidatorV1` immédiatement avant le save. Seul `Validated` autorise l’écriture ; `Invalid` ferme la requête et `DependencyUnavailable` produit une indisponibilité retryable. Aucun fallback city/neighborhood n’existe.

## Address Intent

`AddressLine::fromString` fournit la représentation canonique stricte existante. Une adresse complète est le couple `geographicPlaceId + addressLine canonique`.

- première adresse complète : UUID v4 serveur ;
- même couple physique : intention conservée ;
- PlaceId ou ligne canonique modifié : nouvelle intention ;
- autre fait modifié : intention conservée ;
- snapshot incomplet : aucune intention inventée.

Aucun `AddressId` n’est émis et `AddressIdentityIssuerV1` n’est pas appelé.

## Checksum et complétude

Le checksum SHA-256 porte une sérialisation versionnée et ordonnée de l’identité du snapshot, sa version et ses faits canoniques, y compris l’intention Address déjà émise. L’intention est générée avant le checksum : aucune boucle d’identité. Même intentId, même version et mêmes faits convergent ; un fait différent diverge.

La complétude F4 est une décision de présence conservatrice. Elle ne recopie pas `PropertyTypePolicy` et ne remplace jamais la validation Domain future. BusinessYear n’est ni lu ni stocké.

## Frontière

F4 ne crée aucun Aggregate Property, RegisterProperty, PropertyRegistry write, AddressId, Promotion, Projection ou Search. F5–F7 et RC2 Iteration 11 restent non ouvertes.
