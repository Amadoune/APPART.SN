# Notifications — Compatibility Summary

Le gel Notifications préserve les frontières certifiées avec IdentityAccess,
Moderation, Reservations, ContentSeo et Search. Aucun domaine externe ne devient
owner d'une décision Notifications.

Les contrôleurs HTTP dépendent uniquement des Readers publics V1. Event dépend
des résultats publics, Delivery dépend uniquement d'Event V1 et Outbox dépend
uniquement de Delivery V1. Aucun accès transversal à PostgreSQL, aux mappers ou
à une Infrastructure externe n'est introduit.

Les surfaces Runtime, HTTP, Event, Delivery et Outbox sont certifiées. Transport,
Routing et Consumer restent non ouverts ; aucune compatibilité implicite ne leur
est accordée.
