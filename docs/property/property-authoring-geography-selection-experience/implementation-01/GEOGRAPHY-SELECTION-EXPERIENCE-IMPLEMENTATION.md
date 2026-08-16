# F4-A — Geography Selection Experience Implementation 01

## Objet

Cette implémentation matérialise la frontière d’acquisition Geography de Property Authoring sans ouvrir la persistance F4 :

`Session IAM → HTTP read-only → GeographySelectionReaderV1 → navigation Workspace → preuve de sélection → rejeu serveur`.

## Surface HTTP

- route unique : `GET /api/authoring/geography/selections` ;
- middleware existants : session IAM obligatoire et throttle authoring ;
- query fermée à `type`, `parentPlaceId`, `cursor`, `limit` ;
- `limit` vaut 50 par défaut et reste borné à 1..100 ;
- Country interdit un parent ; les autres types exigent un UUID canonique ;
- le Controller ne compose que `GeographySelectionReaderV1` ;
- réponses `no-store`, sans owner fourni par le client.

Le mapping est fermé : query invalide 422, Available/Empty 200, Missing 404, Corrupted 500 et DependencyUnavailable 503. Un succès expose exclusivement `items`, `nextCursor`, puis `placeId`, `label`, `type`, `parentPlaceId` par item.

## Workspace

L’étape Localisation interroge F1 et construit les niveaux à partir des branches effectivement retournées. Les changements de parent annulent la requête précédente, retirent les descendants et chargent les enfants possibles. Les états loading, vide, manquant, corrompu et indisponible sont explicites ; une indisponibilité bloque la progression.

La preuve locale conserve `geographicPlaceId`, type, parent, curseur d’entrée et limite. Le label ne sert qu’à l’affichage. Les champs legacy city/neighborhood restent transportés pour compatibilité à partir des libellés réellement sélectionnés, mais ne créent ni ne valident aucun PlaceId.

## Rejeu serveur

`DeterministicGeographySelectionReplayValidatorV1` reconstruit la requête F1 avec le contexte déclaré, exige Available et recherche l’égalité exacte de l’ID, du type et du parent. Son résultat fermé est `Validated`, `Invalid` ou `DependencyUnavailable`. Il n’effectue aucune écriture.

## Frontière préservée

Cette livraison n’ajoute aucun champ Geography à `PropertyAuthoringState`, ne modifie ni checksum ni contrat F1, et ne crée ni migration 099, lookup alternatif, Address Intent, Promotion, Projection ou Search.
