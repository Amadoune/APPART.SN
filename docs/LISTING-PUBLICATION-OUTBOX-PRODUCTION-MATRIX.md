# Listing Publication Outbox Production Matrix

| Résultat 4.1D | Transition disponible | Catalogue appelé | Outbox | Résultat 4.1E |
|---|---:|---:|---:|---|
| `Applied` | oui | oui, une fois | `Applied` attendu | `Applied` |
| `AlreadyApplied` | oui | oui, déterministe | `AlreadyApplied` attendu au rejeu | `AlreadyApplied` |
| `Denied` | non | non | aucune écriture | `Denied` inchangé |
| `ConcurrencyConflict` | non | non | aucune écriture | conflit inchangé |
| `PersistenceFailure` | non | non | aucune écriture | échec inchangé |

Chaque événement canonique est enveloppé dans `ListingPublicationDeliveryPayload`. Son `eventId` reste l'identité métier ; le `message_id` produit par la fabrique existante reste l'identité technique. Les quinze types et leur version proviennent exclusivement des catalogues certifiés.
