# Listing Publication Event Routing Runtime Binding Matrix

| Contrat / composant | Implémentation | Cycle de vie | Dépendances |
|---|---|---|---|
| `ListingPublicationEventSerializer` | classe certifiée 4.1EA | singleton paresseux | aucune |
| `ListingPublicationEventDestination` | `PostgreSqlListingPublicationEventInbox` | alias singleton paresseux | PDO, serializer |
| `ListingPublicationEventRouter` | `DurableListingPublicationEventRouter` | alias singleton paresseux | destination |

Runtime Health inspecte le port de destination et le port de routage. Il vérifie uniquement présence, compatibilité et constructibilité.
