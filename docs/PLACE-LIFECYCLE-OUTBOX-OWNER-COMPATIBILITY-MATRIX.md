# Place Lifecycle Outbox Owner — Matrice de compatibilité

| Dimension | Décision Place Lifecycle | Collision évitée |
|---|---|---|
| module | `Geography` | aucun des neuf modules existants |
| schéma | `geography` | aucune table Outbox existante |
| aggregate type | `PlaceLifecycle` | distinct des agrégats existants |
| event namespace | `place.lifecycle.*` | distinct des catalogues existants |
| message identity | convention générique Delivery | aucune réutilisation du `messageId` Transport |
| Writer/Reader | génériques | aucune implémentation spécialisée |
| Worker | générique | aucun Worker concurrent |
| Consumer registration | trois types 4.8F | clés de catalogue uniques |
| Runtime Health | `place_lifecycle_outbox_owner` | capacité réservée unique |

L'identité Outbox future reste celle du contrat générique Delivery. Elle ne
remplace ni `eventId` 4.8F ni `messageId` 4.8G.
