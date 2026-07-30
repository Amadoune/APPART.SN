# Workflow Persistence Mapping Matrix

| Colonne | Source | Règle |
|---|---|---|
| `listing_id` | `ListingId` | identité exacte |
| `version` | commande de persistance | positive, unique et contiguë |
| `previous_state` | `transition.from` | nul uniquement en version 1 |
| `current_state` | état initial ou `transition.to` | valeur fermée |
| `action` | `transition.action` | nulle uniquement en version 1 |
| `transition_checksum` | mapper | SHA-256 déterministe des cinq champs précédents |

Aucun statut de décision ou diagnostic n'est persisté. Le workflow demeure propriétaire de Allowed/Denied.
