# Décision de frontière Address::equals

## Options

| Option | Impact | Décision |
|---|---|---|
| A — inclure AddressId dans `Address::equals()` | change `ChangeAddress` et l’égalité Domain générale | rejetée |
| B — comparaison Promotion plus stricte | localisée à l’idempotence Application | **retenue** |
| C — autre mécanisme | aucun mécanisme existant plus précis démontré | rejetée |

`Address::equals()` reste une égalité descriptive. La future correction compare en plus `existingAddress->id->equals(expectedAddress->id)` dans la frontière Promotion, avec gestion explicite des nulls.

Cette décision ne crée aucune nouvelle règle `ChangeAddress`.
