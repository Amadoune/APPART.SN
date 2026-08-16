# Construction Year Integration

La relation Domain demeure exactement :

`ConstructionYear.value <= BusinessYear.value`.

BusinessYearAuthority fournit uniquement le contexte annuel stable. `PropertyTypePolicy` reste responsable du rejet `futureConstructionYear` et de la règle Land qui interdit ConstructionYear.

Un ConstructionYear supérieur au BusinessYear est un rejet Domain normal, pas un échec de l'autorité temporelle.
