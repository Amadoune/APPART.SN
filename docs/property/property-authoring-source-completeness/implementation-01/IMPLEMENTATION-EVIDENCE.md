# F4 — Implementation Evidence

## Audit avant mutation

- State historique : identité, owner, version, intent/checksum, propertyType, city, neighborhood ;
- store : transaction locale, advisory lock, optimistic locking, replay et corruption fail-closed préservés ;
- onze constructions directes historiques recensées et maintenues compatibles grâce aux paramètres nullable ;
- mapper et deux compositions d’écriture identifiés ;
- migration globale maximale avant F4 : 098 ; numéro 099 libre.

## Preuves d’implémentation

- huit colonnes et huit propriétés de State matérialisées ;
- mapper strict des formes legacy et enrichies ;
- store PostgreSQL étendu en lecture/écriture sans changer sa décision de concurrence ;
- `DeterministicPropertyAuthoringStateEnricherV1` commun aux opérations Authoring et à la surface HTTP ;
- binding nominatif `PropertyAuthoringStateEnricherV1` ;
- composition obligatoire de `GeographySelectionReplayValidatorV1` ;
- Request publique et Request Authoring générique fermées aux autorités techniques ;
- workspace F4-A réutilisé, enrichi uniquement avec les faits owner-authored ;
- city/neighborhood conservés comme labels legacy, jamais comme source de PlaceId.

## Preuves Address Intent

Les tests couvrent première émission, conservation pour changement non physique, conservation après normalisation stricte équivalente, rotation PlaceId et rotation AddressLine. L’identité est créée uniquement côté serveur. Aucun appel à l’Issuer F2 et aucun AddressId Domain.

## Preuves d’absence

Les tests d’architecture interdisent dans F4 : RegisterProperty, PropertyRegistry write, AddressIdentityIssuerV1, BusinessYear, PropertyTypePolicy, Projection et Search. La migration ne contient ni backfill, ni valeur P02, ni `NOT NULL` sur les nouvelles colonnes.
