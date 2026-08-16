# Hierarchy Model

Niveaux réels uniquement :

`Country → Region → Department → City → District → Neighborhood`.

Relations autorisées par `PlaceType::acceptsParent` :

- Region sous Country ;
- Department sous Region ;
- City sous Region ou Department ;
- District sous Department ou City ;
- Neighborhood sous City ou District.

La navigation commence par `type=country,parent=null`, puis chaque requête utilise le `placeId` explicitement choisi comme parent et un type enfant compatible. Le reader ne suppose pas que tous les pays utilisent chaque niveau et ne crée pas de niveau intermédiaire manquant.
