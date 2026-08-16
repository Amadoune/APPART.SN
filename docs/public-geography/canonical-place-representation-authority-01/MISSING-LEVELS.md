# Missing levels

Les niveaux absents admis par `PlaceType::acceptsParent()` restent absents. Aucun nœud synthétique n'est créé.

Exemples valides : Region → City; City → Neighborhood; Department → District. L'ordre relatif root→leaf demeure inchangé.
