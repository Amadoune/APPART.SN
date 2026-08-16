# F7-B — Rapport de validation

## Campagne ciblée

Environnement PHP : 8.5.8.

| Validation | Résultat |
|---|---|
| Unit Promotion F6/F7-B | PASS |
| PostgreSQL Promotion | PASS |
| PostgreSQL Submit ciblé | PASS |
| F2 Address Identity | PASS |
| Architecture Promotion et Submit | PASS |
| Campagne PHPUnit combinée | PASS — 25 tests, 219 assertions |
| PHPStan ciblé | PASS — 0 erreur |
| Pint ciblé | PASS |
| `git diff --check` | PASS |

## Couverture fonctionnelle

- compatibilité canonique complète et ledgerless ;
- AddressId, PlaceId et AddressLine divergents ;
- matrice null/non-null complète ;
- divergences de tous les autres faits Property ;
- replay ledger identique et divergent ;
- persistance PostgreSQL sans doublon ni ledger divergent ;
- poursuite ou arrêt de Submit selon le verdict Promotion.

## Architecture

La garde ciblée confirme que `Address::equals()` demeure descriptif, ChangeAddress demeure inchangé, la Promotion impose l’AddressId et aucune migration 101 n’existe.

## Portée

Aucune campagne frontend n’était applicable. Les campagnes exécutées sont limitées aux surfaces réellement impactées.
