# Account Status Persistence — Mapping Matrix

| Colonne | Bootstrap | Transition |
|---|---|---|
| `account_id` | identité historique | contexte V1 |
| `version` | `0` | `expectedVersion + 1` |
| `entry_kind` | `bootstrap` | `transition` |
| `previous_state` | `NULL` | état source |
| `current_state` | état historique observé | état cible |
| `action` | `NULL` | `suspend` / `reactivate` |
| `actor_id` | `NULL` | acteur explicite |
| `occurred_at` | `NULL` | instant UTC canonique |
| `intent_id` | `NULL` | intention explicite |
| `context_version` | `NULL` | `1` |
| `legacy_account_version` | provenance Account | `NULL` |
| `entry_checksum` | SHA-256 canonique | SHA-256 canonique |

## Contraintes

- clé primaire `(account_id, version)`;
- unicité `(account_id, intent_id)` pour les transitions;
- états limités à `active`, `suspended`;
- forme bootstrap/transition contrôlée;
- machine d'états contrôlée en base;
- checksum vérifié à chaque lecture.
