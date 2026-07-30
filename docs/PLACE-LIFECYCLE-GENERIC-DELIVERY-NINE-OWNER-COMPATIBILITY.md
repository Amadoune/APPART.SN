# Generic Delivery — Compatibilité des neuf owners historiques

| Owner | Payload actuel | Consumer actuel | Impact R2 |
|---|---|---|---|
| ListingLifecycle | générique compatible | générique compatible | aucun |
| RealEstateCatalog | générique compatible | générique compatible | aucun |
| Media | générique compatible | générique compatible | aucun |
| SearchDiscovery | générique compatible | générique compatible | aucun |
| ContentSeo | générique compatible | générique compatible | aucun |
| ReservationLifecycle | interface générique | interface générique | aucun |
| ContactsLeads | interface générique | interface générique | aucun |
| Professionals | interface générique | interface générique | aucun |
| AdministrationAudit | interface générique | interface générique | aucun |

L'amendement ne modifie aucun port PublicProjection. Il aligne uniquement les
deux classes Place Lifecycle existantes sur le protocole déjà consommé par ces
owners.

Les identités restent séparées :

```text
eventId 4.8F
messageId Transport 4.8G
PublicProjectionDeliveryMessageId / IdempotencyKey
```
