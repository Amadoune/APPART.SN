# Property Lifecycle Persistence Mapping Matrix

| Modèle applicatif | Colonne PostgreSQL | Règle |
|---|---|---|
| `PropertyId.value` | `property_id` | UUID exact |
| version fournie | `version` | entier exact |
| `transition.from.value` | `previous_state` | valeur enum exacte |
| `transition.to.value` | `current_state` | valeur enum exacte |
| `transition.action.value` | `action` | valeur enum exacte |
| champs précédents | `transition_checksum` | SHA-256 des cinq valeurs séparées par LF |

Pour l'initialisation, `previous_state` et `action` valent `NULL`, `version` vaut 1 et le checksum encode des chaînes vides à leurs positions. Le mapper ne normalise et ne déduit aucune valeur.
