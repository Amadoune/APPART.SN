# Decision-based lookup

Le payload JSONB V2 contient un revisionVector d'items avec placeId. L'infrastructure peut tester la présence du mutatedPlaceId et paginer par PK `place_id`.

Cette lecture est normativement autorisée uniquement pour découvrir les décisions déjà matérialisées. L'absence d'index JSONB est un sujet de performance, pas un blocage fonctionnel ou une raison de migration immédiate.
