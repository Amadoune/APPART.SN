# Reservation Lifecycle Runtime Binding Matrix

| Contrat ou composant | Résolution | Cycle | Instance partagée |
|---|---|---|---:|
| `ReservationLifecycleWorkflow` | classe certifiée 4.3A | singleton | oui |
| `ReservationLifecycleWorkflowMapper` | mapper certifié 4.3B | singleton | oui |
| `PostgreSqlReservationLifecycleWorkflowRepository` | repository certifié 4.3B | singleton | oui |
| `ReservationLifecycleWorkflowStore` | alias du repository PostgreSQL | singleton par alias | oui |
| `PDO` | connexion PostgreSQL Runtime existante | singleton | oui |

Le repository reçoit exactement le mapper et le PDO résolus par Laravel. Aucun composant HTTP, événementiel ou Outbox n'appartient au graphe.
