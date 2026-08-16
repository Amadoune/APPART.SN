# Business Year Authority

`BusinessYear` est une entrée obligatoire de `RegisterProperty` et de `PropertyTypePolicy`. Il sert à refuser un `ConstructionYear` futur, mais n'est pas persisté dans l'Aggregate.

L'audit observe uniquement des années explicitement construites par les callers/tests et la constante de démonstration P02. Aucune règle Domain ou Application ne stipule que `BusinessYear = year(occurredAt)`.

Décision : ne pas certifier cette dérivation implicitement. Une autorité applicative RealEstateCatalog de calendrier métier doit fournir le BusinessYear effectif pour `occurredAt`, avec version/politique explicite. Le propriétaire, Projection, Search et l'horloge brute ne sont pas owners de cette décision.

Tant que cette autorité n'existe pas ou n'est pas certifiée, la source reste non résolue et la promotion est `IncompleteForPromotion`/indisponible selon le futur contrat fermé.
