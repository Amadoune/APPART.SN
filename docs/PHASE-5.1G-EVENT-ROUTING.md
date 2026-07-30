# Phase 5.1G — Event Routing

| Event | Destinations |
|---|---|
| name changed | private audit |
| email/phone changed | private audit, identity source, notifications |
| closure requested | private audit, notifications |
| closed | private audit, notifications, session invalidation, cross-domain availability |
| reopened | private audit, cross-domain availability |

Le router valide d'abord un aller-retour canonique. Un message corrompu est rejeté sans destination. Les destinations sont fermées et ne contiennent aucune adresse technique de broker.
