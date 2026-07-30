# Professional Status Atomic Event Transaction Policy

`PostgreSqlAggregateOutboxTransaction` est réutilisée comme unique frontière transactionnelle. Elle interdit les transactions imbriquées, commit ensemble journal, contexte et Outbox, et annule l'ensemble après toute exception.

Un rejeu identique converge vers `AlreadyApplied` et le Writer retourne `AlreadyApplied` sans message supplémentaire. Deux requêtes concurrentes identiques convergent vers `Applied + AlreadyApplied`, avec une transition, un contexte et un message.
