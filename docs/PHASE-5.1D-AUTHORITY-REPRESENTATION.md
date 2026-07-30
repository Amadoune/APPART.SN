# Phase 5.1D — Authority Representation

## Objet

Représenter la Phase 5.1D sans aucune modification de son mécanisme métier,
après certification et fermeture de
`A-5.1-IAM-PERSISTENCE-BOUNDARY-01`.

## Statuts

```text
A-5.1-IAM-PERSISTENCE-BOUNDARY-01
→ GO CERTIFIÉ
→ FERMÉ

Phase 5.1D
→ NO GO CERTIFIÉ
→ OUVERTE
→ À CERTIFIER DÉFINITIVEMENT

Phase 5.1E
→ FERMÉE
```

## Mécanisme représenté, inchangé

- source exclusive : Snapshot V1 via son mapper d'enclave ;
- Profiles et Claims déterministes et idempotents ;
- normalisation versionnée `iam-profile-v1` ;
- migration additive 052 ;
- claims historiques réservées durablement ;
- divergences qualifiées et quarantaines sans écriture partielle ;
- cutover atomique `Historical → Profile` ;
- manifest et rapport sans PII ;
- rollback transactionnel vers Historical.

## Frontières

Aucune modification de Historical Account, Account, AccountRegistry,
Snapshot V1, Account Status, Runtime, HTTP, Event, Delivery, Outbox ou Provider.
Les migrations 041–052 et les comportements de persistence restent inchangés.

## Preuves représentées

```text
Architecture complète   :   596 tests, 45 146 assertions — PASS
Suite applicative       : 2 739 tests, 53 101 assertions — PASS
PostgreSQL 5.1C + 5.1D :     9 tests,     46 assertions — PASS
PHPStan                 :     0 erreur — PASS
Pint                    :     PASS
git diff --check        :     PASS
```

## Proposition

Toutes les gates 5.1D sont satisfaites et la réserve unique est levée.

```text
Phase 5.1D
→ GO DÉFINITIF PROPOSÉ

Phase 5.1E
→ RESTE FERMÉE JUSQU'AU PRONONCÉ D'AUTORITÉ
```
