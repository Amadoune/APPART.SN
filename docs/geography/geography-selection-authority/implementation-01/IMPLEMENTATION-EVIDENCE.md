# F1 — Implementation Evidence

## Historique et réouverture

Le NO GO initial démontrait l'absence d'une source Place autoritative. F0 a levé précisément ce blocage. La réouverture compose exclusivement cette autorité nouvellement certifiée.

## Preuves de code

- contrat `GeographySelectionReaderV1` et port read-only `GeographySelectionSource` ;
- query validée et DTO minimal ;
- cinq statuts fermés, sans fallback ;
- orchestrateur `DeterministicGeographySelectionReader` ;
- curseur opaque versionné, fingerprinté et vérifié ;
- adapter `PostgreSqlGeographySelectionSource` sur `geography.places` ;
- Provider et binding nominatif vers la composition réelle.

## Preuves comportementales

- racines Country ;
- enfants sous parent autoritatif ;
- ordre stable et pagination keyset ;
- rejet d'un curseur utilisé sur une autre sélection ;
- exclusion des Places disabled ou merged ;
- parent absent → `Missing` ;
- hiérarchie incompatible → `Corrupted` ;
- source vide → `Empty` ;
- indisponibilité → `DependencyUnavailable` ;
- binding framework réel.

## Absences démontrées

Aucun accès à Public Projection, Search, Property Authoring ou P02. Aucun élargissement de `PlaceRegistry`. Aucune migration supplémentaire.
