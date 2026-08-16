# Relational Model

## geography.places

| Colonne | Type / contrainte |
|---|---|
| id | uuid primary key |
| official_name | varchar(120) not null |
| code | varchar(32) not null |
| type | varchar constrained aux six PlaceType |
| country_code | char(2) not null |
| parent_place_id | uuid null, FK places(id) RESTRICT |
| latitude / longitude | decimal null ensemble, bornes Domain |
| enabled | boolean not null |
| merged_into_place_id | uuid null, FK places(id) RESTRICT |
| aggregate_version | integer not null check >= 1 |

Contraintes : unique `(country_code, code)` ; check merged implique enabled=false ; id distinct de parent et merged target ; coordonnées toutes deux null ou toutes deux présentes. Les règles type/parent, pays commun et cible merge compatible restent Domain.

## geography.place_aliases

Clé `(place_id, normalization_key)`, FK RESTRICT/CASCADE avec root selon suppression interdite (aucun delete use case), name varchar(120), normalization_key varchar(120), recorded_at timestamptz. Aucun unique global sur labels.
