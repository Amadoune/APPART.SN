# MEDIA AUTHORING PUBLIC SURFACE 02

## Surface publique

La surface owner-scoped expose exclusivement :

- `POST /api/authoring/properties/{propertyId}/media` : upload multipart ;
- `GET /api/authoring/properties/{propertyId}/media` : relecture de collection ;
- `DELETE /api/authoring/properties/{propertyId}/media/{mediaId}` : archive via le use case certifié.

Toutes les routes exigent `RequireIdentityAccessSession`. L'owner provient uniquement de l'attribut IAM de la requête et aucun champ owner n'est accepté.

## Composition

`DeterministicMediaAuthoringHttpRuntime` compose sans nouvelle décision :

1. contrôle owner-scoped par `PropertyAuthoringStore` ;
2. stockage via `MediaBinaryStorageAuthorityV1` ;
3. promotion via `MediaAssetReadinessV1` ;
4. attachement via `AttachReadyMediaAssetV1` ;
5. relecture via `MediaCollectionRegistry`.

`assetId`, `collectionId` et intents techniques sont dérivés de façon déterministe. Aucun identifiant owner ne vient du payload. Un replay du même upload converge sur les capacités idempotentes existantes.

## Archive

La suppression physique n'est pas ouverte. La surface utilise exclusivement `ArchiveMedia`. Conformément à l'invariant existant, archiver le média primaire exige `replacementMediaId`. Le média primaire unique ne peut donc pas être archivé artificiellement.

## Compatibilité

Les brouillons et collections historiques restent inchangés. Aucune migration, projection, règle métier ou persistance parallèle n'est ajoutée.
