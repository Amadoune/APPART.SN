# Reservation Lifecycle Persistence Mapping Matrix

| Modèle | Colonne | Mapping |
|---|---|---|
| `ReservationId::value` | `reservation_id` | UUID canonique |
| version | `version` | entier inchangé |
| `transition.from` | `previous_state` | valeur enum |
| état initial ou `transition.to` | `current_state` | valeur enum |
| `transition.action` | `action` | valeur enum |
| ligne logique | `transition_checksum` | SHA-256 des cinq champs séparés par LF |

Une initialisation utilise la version 1, `previous_state = NULL` et `action = NULL`. Une transition utilise tous les champs. La reconstruction vérifie le UUID, l'état, la version positive et le checksum avant de produire `ReservationLifecycleStoredState`.
