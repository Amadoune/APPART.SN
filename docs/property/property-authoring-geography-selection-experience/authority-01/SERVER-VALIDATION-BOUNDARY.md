# Server Validation Boundary

## Validation au save Authoring

Le navigateur transmet :

- `geographicPlaceId` sélectionné ;
- contexte de preuve non persistant : `type`, `parentPlaceId`, curseur d’entrée de la page et `limit`.

Ce contexte n’est pas une autorité. Le serveur reconstruit la même `GeographySelectionQuery`, réexécute `GeographySelectionReaderV1` et exige :

1. statut `Available` ;
2. présence exacte de `geographicPlaceId` dans les items retournés ;
3. égalité du type et du parent avec le contexte rejoué.

Comme F1 ne retourne que des Places enabled, non merged, de type et parent exacts, la présence dans cette page démontre l’acceptabilité au moment du save. Aucun lookup SQL, accès à `PlaceRegistry`, extension de F1 ou confiance dans un appel GET antérieur.

Une query invalide, un curseur altéré, un item absent, Empty, Missing, Corrupted ou DependencyUnavailable refuse la sauvegarde. Aucun fallback vers `city`/`neighborhood`.

## Atomicité

La validation est effectuée immédiatement avant l’écriture owner-scoped. Elle ne rend pas Geography et Authoring transactionnels ensemble ; elle qualifie l’état observé. L’écriture Authoring conserve sa transaction locale, son optimistic locking et son replay.

## TOCTOU

La validation Authoring ne remplace jamais la validation de Promotion. F6 relira ultérieurement `GeographicPlaceCatalog::statusOf` avant `RegisterProperty`. Une Place devenue disabled ou merged entre save et Promotion rend la Promotion fail-closed.
