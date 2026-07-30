# Listing Publication Runtime Binding Matrix

| Capacité | Déclaration | Cycle de vie | Dépendances | Effet au bootstrap |
|---|---|---|---|---|
| Workflow | `ListingPublicationWorkflow` | Singleton paresseux | Aucune | Aucun |
| Mapper | `ListingPublicationWorkflowMapper` | Singleton paresseux | Aucune | Aucun |
| Repository | `PostgreSqlListingPublicationWorkflowRepository` | Singleton paresseux | `PDO`, mapper | Aucun |
| Store | alias `ListingPublicationWorkflowStore` | Instance du repository | Repository | Aucun |
| Connexion | `PDO` Runtime existant | Singleton paresseux | Connexion Laravel `pgsql` | Aucune requête |

Il existe exactement une déclaration pour chaque composant et une seule implémentation de production du store.
