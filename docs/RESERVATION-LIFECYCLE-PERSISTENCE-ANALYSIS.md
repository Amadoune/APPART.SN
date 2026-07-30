# Reservation Lifecycle Persistence Analysis

## Décision

Le workflow 4.3A reste inchangé et demeure l'unique propriétaire des décisions. La persistance reçoit uniquement un état initial ou une `ReservationLifecycleTransition` déjà certifiée.

Comme aucun Domain Reservation n'existe, `ReservationId` est un Value Object du contrat applicatif de persistance. Il identifie le journal sans introduire d'Aggregate ni de modèle Domain.

## Stratégie

- journal PostgreSQL append-only par `(reservation_id, version)` ;
- version initiale 1, puis continuité stricte contrôlée sous verrou ;
- verrou advisory transactionnel par réservation ;
- checksum SHA-256 déterministe couvrant identité, version, états et action ;
- lecture courante ordonnée et limitée à une ligne ;
- onze transitions protégées par une contrainte PostgreSQL explicite ;
- transaction externe respectée sans transaction imbriquée.

Le repository ne contient ni workflow, ni table de décision, ni horloge.
