# Negative Geography Evidence

| Observation | Statut | Readiness positive |
|---|---|---|
| UUID absent | `NotFound` | non |
| Place disabled | `Disabled` | non |
| Place merged | `Merged` | non |
| type non adressable | `NotAddressable` | non |
| snapshot corrompu | exception | non, fail-closed |
| PostgreSQL indisponible | exception | non, retry après restauration |

Seul `Usable` autorise RegisterProperty à poursuivre. Une merge target n’est jamais suivie automatiquement. Aucun incident technique n’est réduit en statut métier et aucun résultat négatif ne devient une readiness positive.
