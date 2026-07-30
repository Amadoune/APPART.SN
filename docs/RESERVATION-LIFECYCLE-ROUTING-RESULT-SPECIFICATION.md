# Reservation Lifecycle Routing Result Specification

## Modèle fermé

| Statut PHP | Valeur canonique | Sens contractuel |
|---|---|---|
| `Stored` | `stored` | l'enveloppe a été stockée pour la première fois |
| `AlreadyStored` | `already_stored` | la même enveloppe était déjà stockée |
| `CorruptedEnvelope` | `corrupted_envelope` | l'enveloppe ou son contenu n'est pas intègre |
| `PersistenceCorrupted` | `persistence_corrupted` | la persistance n'a pas pu produire un résultat fiable |

`ReservationLifecycleRoutingResult` contient exactement un `ReservationLifecycleRoutingStatus`. Il est final et readonly. Aucun statut implicite, diagnostic textuel, exception technique ou branche `default` n'appartient au contrat.

Cette spécification ne définit pas encore comment les statuts sont produits. Cette responsabilité commencera avec l'implémentation 4.3G.
