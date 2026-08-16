# Feature Frontend Dependency

Trois surfaces Blade invoquent directement `@vite` :

- `authoring-workspace` ;
- `public-listing` ;
- `layouts.public`, utilisé par Home, Login, Owner Dashboard, Search et Publication Review.

Un audit statique recense 46 méthodes Feature susceptibles d'atteindre ces vues. Aucun `withoutVite()`, fake Vite ou manifest de test n'existe dans la suite.

Le test Resume n'est donc pas un cas isolé : les Feature tests HTTP certifient des vues réellement composées avec les assets de production.
