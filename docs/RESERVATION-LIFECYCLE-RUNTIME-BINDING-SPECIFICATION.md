# Reservation Lifecycle Runtime Binding Specification

La racine `PublicProjectionRuntimeServiceProvider` demeure l'unique Service Provider de composition.

- `ReservationLifecycleWorkflow` : singleton paresseux.
- `ReservationLifecycleWorkflowMapper` : singleton paresseux.
- `PostgreSqlReservationLifecycleWorkflowRepository` : singleton paresseux.
- `ReservationLifecycleWorkflowStore` : alias exclusif vers le repository PostgreSQL.
- `PDO` : singleton PostgreSQL Runtime existant.

L'alias et la classe concrète partagent la même instance. Aucun Fake, Null Object, fallback ou deuxième binding n'est autorisé.
