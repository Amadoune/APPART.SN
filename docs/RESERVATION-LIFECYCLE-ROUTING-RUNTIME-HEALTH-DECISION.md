# Reservation Lifecycle Routing Runtime Health Decision

## Décision

Runtime Health est explicitement étendu avec deux capacités requises :

- `ReservationLifecycleInboxStore` ;
- `ReservationLifecycleEventRouter`.

Cette extension est retenue parce que le routeur et sa destination deviennent des dépendances obligatoires du futur pipeline Worker. Une simple présence de bindings non inspectée permettrait un démarrage `Healthy` malgré un graphe incomplet.

## Portée de l'inspection

Runtime Health vérifie exclusivement :

- la déclaration de chaque composant ;
- la présence du binding ;
- la compatibilité avec son contrat ;
- la constructibilité de ses dépendances.

Les exigences référencent uniquement `ReservationLifecycleInboxStore` et `ReservationLifecycleEventRouterPort`, jamais les classes PostgreSQL ou le routeur concret. La construction du repository ne lance aucune requête. L'inspection n'appelle ni `store()` ni `route()`.

Le Runtime complet doit rester `Healthy` sans dépendre de la présence effective de la table Inbox au bootstrap.
