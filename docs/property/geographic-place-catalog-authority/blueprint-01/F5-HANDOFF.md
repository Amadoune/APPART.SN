# F5 Handoff

F5 Public Property Source Completeness ne peut être rouvert qu’après un chantier F5-A Implementation ayant fourni les preuves suivantes :

- autorité explicite et fermée d’adressabilité des `PlaceType` ;
- adaptateur productif read-only ;
- binding productif ;
- mappings `Usable`, `NotFound`, `Disabled`, `Merged`, `NotAddressable` exécutables ;
- priorité merge/disabled ;
- corruption et indisponibilité fail-closed ;
- tests unitaires de chaque branche ;
- test de composition du binding ;
- tests PostgreSQL sur Places usable, disabled, merged et missing ;
- test d’architecture sans SQL Application, Projection, Search ni dépendance inverse ;
- composition de `RegisterProperty` sans fake.

F5 devra ensuite refaire sa matrice complète et sa preuve de source assembly. F6 reste fermée jusqu’au GO de cette recertification.

La condition historique relative à la policy est levée par F5-A1. Le prochain chantier autorisé après GO de cette Completion est donc **F5-A Geographic Place Catalog Implementation 01**.

Cette Implementation devra matérialiser la policy déterministe, son résultat fermé, l’adapter, le Provider/binding et les tests définis dans `IMPLEMENTATION-GATE.md`. Aucune migration n’est attendue.

Après son GO seulement, F5 Public Property Source Completeness pourra être rouvert et refaire sa matrice complète sans fake ni fallback. F6 reste fermée jusqu’au GO F5.
