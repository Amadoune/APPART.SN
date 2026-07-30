# Delivery Runtime Composition — analyse

## Décision

Le Sprint 3.6H complète la racine de composition Laravel sans modifier le protocole Delivery. Les implémentations PostgreSQL et les politiques applicatives certifiées sont réutilisées telles quelles.

## Graphe composé

`PublicProjectionDeliveryWorker` reçoit par injection :

- le Reader, le Writer et le ClaimManager Outbox PostgreSQL ;
- le registre explicite des cinq événements officiels ;
- la retry policy déterministe avec backoff fixe ;
- l'horloge système de production ;
- les identités Worker et Consumer configurées ;
- les limites de batch et de lease configurées.

La résolution est paresseuse. L'enregistrement du Provider ne lit pas l'Outbox, ne claim aucun message et n'exécute aucun Consumer.

## Périmètre

La seule nouvelle implémentation est `SystemPublicProjectionDeliveryClock`, explicitement requise par le sprint. Elle fournit l'instant opérationnel du claim et ne porte aucune version métier. Aucune persistance, règle métier ou convention Delivery n'est ajoutée.
