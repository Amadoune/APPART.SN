# Business Year Audit

`BusinessYear` est un Value Object entier. `RegisterProperty` le reçoit du caller et `PropertyTypePolicy` l'utilise seulement pour refuser un ConstructionYear futur. Aucun factory, calendrier métier, clock Property ou policy temporelle ne produit cette valeur.

Constats :

- les tests et commandes locales construisent explicitement `BusinessYear::fromInt(2026)` ;
- `PropertyMapper` reçoit un `validationYear` externe lors de la reconstitution ;
- le clock observé dans Public Projection appartient au worker de projection et ne porte aucune autorité Property ;
- aucune documentation normative auditée ne définit `year(occurredAt)` ;
- timezone, changement d'année et stabilité au replay ne sont pas spécifiés.

Les options année de occurredAt, horloge système ou valeur caller restent donc des possibilités non décidées, pas des autorités existantes.
