# Port Property-to-Listings

`PropertyListingsResolver::readPage(propertyId, checkpoint, limit)` retourne :

| Statut | Identités | Checkpoint | Terminé | Sens |
|---|---:|---|---:|---|
| `Found` | 1..N | présent | non | une autre page existe |
| `Empty` | 0 | absent | oui | aucune cible |
| `Completed` | 1..N | absent | oui | dernier lot |
| `InvalidIdentity` | 0 | absent | oui | Property invalide |
| `Corrupted` | 0 | absent | oui | requête non interprétable |

Diagnostics fermés : `None`, `InvalidPropertyIdentity`, `InvalidLimit`, `InvalidCheckpoint`,
`CheckpointForAnotherProperty`, `PersistedIdentityNotMappable`.

Le client conserve le checkpoint uniquement lorsque le statut est `Found`. Rejouer le même appel
sur un état durable identique rend exactement la même page.
