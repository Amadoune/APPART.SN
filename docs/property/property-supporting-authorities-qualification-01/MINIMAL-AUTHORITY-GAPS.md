# Minimal Authority Gaps

## Geography Selection Authority

- Owner candidat : Geography Application.
- Responsabilité unique : exposer les Places explicitement sélectionnables.
- Entrée minimale : critères structurés/pagination et contexte de lecture, jamais un owner client arbitraire.
- Sortie minimale : items avec PlaceId et libellés certifiés ; `Available`, `Empty`, `Missing`, `DependencyUnavailable` ou catalogue fermé équivalent.
- Invariants : read-only, déterministe, lifecycle respecté, aucun ID construit depuis texte libre.

## Address Identity Authority

- Owner candidat : RealEstateCatalog Application.
- Responsabilité unique : émettre l'AddressId d'une intention de nouvelle Address.
- Entrée minimale : propertyId, commandId/intention stable et version.
- Sortie minimale : AddressId ou conflit/indisponibilité fermé.
- Invariants : replay stable, unicité, collision fermée, aucun ID fourni par Projection.

## Business Year Authority

- Owner candidat : RealEstateCatalog Application.
- Responsabilité unique : déterminer le contexte annuel utilisé par PropertyTypePolicy.
- Entrée minimale : instant métier stable et version de politique/calendrier.
- Sortie minimale : BusinessYear ou indisponibilité fermée.
- Invariants : timezone explicite, passage d'année déterministe, replay inchangé.

Ces formes sont des périmètres de futurs Blueprints, pas des contrats d'implémentation.
