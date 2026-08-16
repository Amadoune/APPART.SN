# Validation Report

## Résultats

| Campagne | Résultat |
|---|---|
| Unit + Feature + Architecture Resume | PASS — 12 tests, 42 assertions |
| Régression ciblée Authoring/Media/Geography | PASS — 45 tests, 236 assertions |
| PostgreSQL Resume zéro mutation | PASS — 1 test, 8 assertions |
| PHPStan global | PASS — 0 erreur |
| Pint ciblé | PASS |
| Vite | PASS |
| `git diff --check` | PASS |

Vite émet uniquement l'avertissement optionnel existant relatif à `fontaine`.

## Limite terminale

La preuve du draft RC2 existant est FAIL : les cinq stores requis ne contiennent plus le PropertyId/ListingId imposé. Ce critère GO obligatoire ne peut pas être remplacé par le fixture PostgreSQL autonome.

## Recertification 01

- nouveau parcours productif owner-scoped : PASS ;
- GET Resume HTTPS : HTTP 200 ;
- reload Resume : HTTP 200 ;
- IDs et versions 1/1 conservés ;
- Geography Dakar City et un Media restaurés ;
- step 6 restauré ;
- fingerprint zéro mutation : identique ;
- campagne destructive DB B après création du draft : PASS, draft DB A inchangé ;
- PostgreSQL représentatif Isolation : 9 tests, 58 assertions ;
- PostgreSQL Resume post-draft : 1 test, 8 assertions.
