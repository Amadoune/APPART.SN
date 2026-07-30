# Listing Publication HTTP Runtime Analysis

## Frontière

Le Runtime HTTP expose la capacité certifiée par un adaptateur unique. Il valide uniquement la forme de l'identifiant, de l'action, de la version attendue et des deux instants explicites. Il construit ensuite la requête applicative et appelle exclusivement `ListingPublicationEventOrchestrator`.

Le contrôleur ne lit aucun état, ne choisit aucune transition et ne connaît ni workflow, ni store, ni PostgreSQL, ni Outbox.

## Décisions

- une route POST représente une demande de transition et non une ressource de projection ;
- l'action est obligatoire et limitée aux quatorze actions exécutables certifiées ; `Unknown` demeure un diagnostic interne du workflow et n'est pas une commande HTTP valide ;
- `occurredAt` et `recordedAt` sont obligatoires au format UTC canonique avec microsecondes ;
- la réponse ne contient que le statut applicatif et son diagnostic exact éventuel ;
- le mapping des cinq statuts est fermé par un `match` sans branche par défaut.

## Sécurité architecturale

Aucune horloge, identité aléatoire, règle d'état, transaction ou compensation n'est introduite. Le Runtime Health et le graphe de production certifié sont réutilisés sans extension fonctionnelle.
