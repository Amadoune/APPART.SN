# Empty Facets

`facets=[]` est normativement valide en v1.

Le Domain l'accepte : `SearchProjection` ne requiert aucune cardinalité minimale et `SearchFacetPolicy::govern([])` retourne une liste vide. La présente autorité transforme cette permissivité en choix produit v1 explicite.

Ce choix est stable pour toute annonce v1 et ne constitue pas un fallback en cas de source manquante.
