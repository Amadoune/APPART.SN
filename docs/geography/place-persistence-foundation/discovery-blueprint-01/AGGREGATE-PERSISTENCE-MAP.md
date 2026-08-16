# Aggregate Persistence Map

| État Aggregate | Persistance cible | Rôle |
|---|---|---|
| PlaceId | places.id UUID PK | identité |
| officialName | places.official_name | fait courant |
| PlaceCode | places.code | identité métier locale au pays |
| PlaceType | places.type | enum Domain sérialisée |
| CountryCode | places.country_code | invariant pays |
| parent | places.parent_place_id nullable | relation hiérarchique ; type relu sur parent |
| Coordinates | latitude/longitude nullable ensemble | fait optionnel |
| aliases | place_aliases(place_id, name, normalization_key, recorded_at) | collection complète |
| enabled | places.enabled | état courant |
| mergedInto | places.merged_into_place_id nullable | cible de fusion |
| version | places.aggregate_version | optimistic locking, initiale 1 |

Les événements enregistrés ne sont pas l'état Aggregate. Lifecycle et Projection restent distincts. Aucun timestamp root supplémentaire n'est requis par `Place::reconstitute`.
