# Binding Model

## Cible future

`GeographicPlaceCatalog` doit être lié nominativement à un adaptateur Geography-backed unique, enregistré par un Provider possédé par l’intégration RealEstateCatalog.

L’adaptateur peut être singleton/lazy comme objet stateless. Sa dépendance productive est `PlaceRegistry`, déjà lié par F0 à `PostgreSqlPlaceRepository`. Il n’ouvre aucune connexion et ne possède aucun SQL.

## Graphe

`RegisterProperty` → `GeographicPlaceCatalog` → adaptateur → `PlaceRegistry` → `PostgreSqlPlaceRepository`.

Il n’existe aucun arc Geography → RealEstateCatalog, aucune dépendance vers F1, HTTP, Projection ou Search, donc aucun cycle.

## Condition préalable

Le Provider et le binding ne peuvent être créés tant que la policy d’adressabilité n’est pas qualifiée. Le binding d’un mapping incomplet rendrait potentiellement `Usable` une Place dont l’adressabilité n’est pas démontrée.
