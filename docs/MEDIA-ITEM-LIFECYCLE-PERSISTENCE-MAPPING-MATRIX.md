# Media Item Lifecycle Persistence Mapping Matrix

| Élément contractuel | Colonne |
|---|---|
| identité opaque | `media_id` |
| ordre métier | `version` |
| état source | `previous_state` |
| état cible | `current_state` |
| action | `action` |
| intégrité | `transition_checksum` SHA-256 |

Le checksum canonique concatène uniquement ces cinq valeurs métier et la version. Aucune donnée de collection n'est mappée.
