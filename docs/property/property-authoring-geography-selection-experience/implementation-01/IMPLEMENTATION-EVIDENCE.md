# F4-A — Implementation Evidence

## Composition livrée

- contrat et résultat fermé du validateur de rejeu dans `app/Application/PropertyAuthoringGeographySelection/Contract` ;
- implémentation déterministe du rejeu dans `app/Application/PropertyAuthoringGeographySelection` ;
- Request stricte, Controller invocable et Provider nominatif ;
- enregistrement du Provider et route dans le groupe authoring IAM/throttlé existant ;
- navigation hiérarchique, annulation, reset, pagination et preuve locale dans le workspace existant ;
- styles responsive limités aux nouveaux niveaux Geography.

## Preuves de frontière

- aucune identité propriétaire n’est acceptée par la query ;
- le Controller ne reçoit que `GeographySelectionReaderV1` ;
- aucun Repository, SQL, PlaceRegistry, Search ou Projection dans la surface ;
- un UUID déclaré ne vaut jamais preuve : le validateur rejoue F1 ;
- les erreurs de validation et de lecture sont minimales et `no-store` ;
- aucun write Geography et aucune persistance Authoring n’ont été ajoutés ;
- aucune migration 099 n’existe.

## Preuves Workspace

Les tests d’architecture du workspace vérifient le chargement Country, les branches enfants optionnelles, `AbortController`, le reset des descendants, `nextCursor`, les cinq éléments du contexte de preuve et le blocage indisponible. Le build Vite compile la composition réelle.

## Compatibilité

Les champs legacy city/neighborhood demeurent présents pour les consommateurs existants. Ils sont alimentés uniquement par le chemin sélectionné et restent non autoritatifs. Les anciens brouillons ne sont ni migrés ni backfillés.
