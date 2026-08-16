# Facet Optionality

Une collection vide est techniquement valide pour `SearchProjection` et `SearchFacetPolicy::govern([])`. Cette tolérance structurelle ne constitue pas une décision produit autorisant une projection sans facettes.

L'optionnalité productive de chaque clé reste indéterminée. Elle ne peut pas servir à contourner le blocage RC2.
