# Reservation Lifecycle HTTP Runtime Analysis

## Décision

L'endpoint Reservation Lifecycle est un adaptateur de commande strict. Il valide uniquement le transport, construit `ReservationLifecycleAtomicEventRequest`, appelle une fois `ReservationLifecycleAtomicEventOrchestrator`, puis délègue la représentation HTTP au mapper fermé.

Le contrôleur ne connaît ni workflow, ni store, ni PostgreSQL, ni Outbox. Une exception inattendue est absorbée dans le résultat applicatif `PersistenceCorrupted`, puis rendue comme indisponibilité HTTP sans détail technique.

Runtime Health demeure inchangé à 27 capacités : le contrôleur et le mapper sont auto-résolus par Laravel et ne constituent pas de nouvelles capacités de fondation.
