# F4 Handoff

## Livrables attendus de F4-A Implementation

- route unique GET exécutable ;
- Request/query fermée ;
- Controller dépendant de `GeographySelectionReaderV1` ;
- mapping HTTP des cinq statuts ;
- DTO minimal ;
- intégration hiérarchique du workspace ;
- transport du `geographicPlaceId` et du contexte de preuve ;
- composant serveur de rejeu F1 utilisable immédiatement avant le save ;
- tests Feature, Architecture et navigateur correspondants.

## Responsabilités conservées par F4

F4 recevra une identité sélectionnée et revalidable, puis restera seul owner de :

- ajout de `geographicPlaceId` au `PropertyAuthoringState` ;
- sept autres faits du snapshot cible ;
- checksum canonique ;
- version, replay et optimistic locking ;
- migration additive 099 et rollback ;
- génération et rotation serveur d’`AddressIntentId` ;
- qualification `IncompleteForPromotion` des snapshots historiques.

F4-A ne persiste rien, ne crée aucun AddressIntentId, AddressId, Aggregate Property ou Promotion.

## Séquencement

1. certifier F4-A Authority ;
2. ouvrir et certifier F4-A Implementation ;
3. seulement ensuite rouvrir F4 Source Completeness.
