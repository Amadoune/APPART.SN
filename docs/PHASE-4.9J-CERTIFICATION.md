# Phase 4.9J — Account Status Outbox Compatibility

## Gates

```text
J1 Owner
→ SATISFAIT

J2 Payload et checksum
→ SATISFAIT

J3 Catalogue Delivery
→ SATISFAIT

J4 Mapper Restore
→ SATISFAIT

J5 Consumer routed-v1
→ SATISFAIT
```

## Livrables

- contrats génériques Routed Delivery V1 ;
- destination canonique et routing proof SHA-256 ;
- port Consumer `consumeRouted` additif ;
- mode générique explicite `legacy | routed-v1` ;
- extension additive du catalogue et du Mapper ;
- conservation générique Writer/Reader ;
- sélection générique du Worker ;
- owner `IdentityAccess ↔ identity_access` ;
- migration 043 additive et réversible ;
- quatre tables Outbox exclusivement dans `identity_access`.

## Garanties

- Router 4.9H seul propriétaire de la destination ;
- Consumer 4.9I seul propriétaire de la consommation ;
- aucune destination implicite ;
- aucune branche Worker spécifique à Account Status ;
- aucune spécialisation Writer, Reader, Mapper ou Worker ;
- dix owners historiques inchangés en mode legacy ;
- `eventId`, message de transport, idempotency key et message générique
  demeurent distincts ;
- routing proof persistée et restaurée fidèlement ;
- migrations 041 et 042 inchangées ;
- aucune publication réelle ni HTTP.

## Validations

```text
Tests ciblés Unit / Runtime / Architecture : 31 / 31, 115 assertions
PostgreSQL ciblé PublicProjectionOutbox     : 38 / 38, 321 assertions
Architecture complète                      : 584 / 584, 44 330 assertions
Suite complète                             : 2 709 / 2 709, 52 214 assertions
Runtime Health                             : Healthy — 58 capacités
PHPStan                                    : 0 erreur
Pint                                       : PASS
git diff --check                           : PASS
```

La baseline PostgreSQL globale d'entrée était `559 / 559`, `2 372
assertions`. Le sprint revendique la campagne ciblée exhaustive du domaine
Outbox ; il ne substitue pas un total global non observé à cette preuve.

## Verdict officiel

```text
4.9J-R3
→ GO CERTIFIÉ ET FERMÉ

4.9J
→ GO CERTIFIÉ ET FERMÉ

4.9K
→ OUVERT APRÈS CERTIFICATION DE 4.9J
```
