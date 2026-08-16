# Certification Note

## Résultat

La frontière est entièrement qualifiée :

- `SearchMissing` est produit par l'absence d'une ligne `public_search_decisions` pour le ListingId ;
- le reader, le binding et le store productifs existent ;
- la Projection consomme uniquement la version finale Search pour son watermark ;
- SearchDiscovery est l'owner normatif de la décision ;
- aucun chemin productif ne matérialise cette décision depuis Published ;
- les commandes locales existantes sont des procédures de démonstration spécifiques, pas un handoff générique ;
- l'UX Search publique est en aval du Projection Store et demeure fermée ;
- la vue `confirmed` masque séparément `NotReady`.

## Cause racine unique

**Source technique autoritative SearchDecision non matérialisée.**

## Suite autorisée

Ouvrir `PUBLIC SEARCH DECISION MATERIALIZATION AUTHORITY 01`, puis seulement son implémentation certifiée. Après ces GO, reprendre le Listing RC2 existant au replay Projection.

## Verdict

**GO PROPOSÉ — APPART.SN PUBLIC LISTING PROJECTION — SEARCH SOURCE DEPENDENCY AUTHORITY 01**
