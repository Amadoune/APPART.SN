# Phase 5.1D — Cutover Certification

## Matrice

| Critère | État |
|---|---|
| lecture exclusive Snapshot V1 | SATISFAIT |
| aucune écriture Historical Account | SATISFAIT |
| Profile et Claims déterministes/idempotents | SATISFAIT |
| normalisation versionnée | SATISFAIT |
| réservation permanente des anciennes claims | SATISFAIT |
| divergences fermées et quarantaine | SATISFAIT |
| rapport sans PII | SATISFAIT |
| transfert d'autorité explicite | SATISFAIT |
| transaction atomique | SATISFAIT |
| rollback sans double autorité | SATISFAIT |
| Runtime/HTTP/Event/Outbox absents | SATISFAIT |
| frontières gelées intactes | SATISFAIT |

## Preuves ciblées

```text
PostgreSQL 18.x
3 tests, 21 assertions — PASS

Architecture 5.1D
2 tests, 12 assertions — PASS

PHPStan ciblé
0 erreur — PASS

PHPStan global
0 erreur — PASS

Pint global
PASS

git diff --check
PASS
```

Les preuves couvrent seed multi-compte, ordre d'entrée indépendant, rejeu,
transfert explicite, duplication historique, quarantaine sans écritures
partielles et rollback complet.

## Réserve architecturale levée

```text
A-5.1-IAM-PERSISTENCE-BOUNDARY-01
→ GO CERTIFIÉ
→ FERMÉ
```

L'amendement a supprimé la dépendance `Application → Infrastructure` et
reconfiné Snapshot V1 dans Historical Account sans modifier le mécanisme
5.1D. La campagne globale est désormais verte :

```text
Architecture complète   :   596 tests, 45 146 assertions — PASS
Suite applicative       : 2 739 tests, 53 101 assertions — PASS
PostgreSQL 5.1C + 5.1D :     9 tests,     46 assertions — PASS
PHPStan                 :     0 erreur — PASS
Pint                    :     PASS
git diff --check        :     PASS
```

## Représentation à l'autorité

```text
Phase 5.1D
→ NO GO CERTIFIÉ
→ OUVERTE
→ REPRÉSENTÉE POUR CERTIFICATION DÉFINITIVE

Phase 5.1E
→ FERMÉE
```

Le mécanisme métier, les migrations et les preuves PostgreSQL sont inchangés.
Le motif unique du NO GO n'existe plus. Le GO définitif de 5.1D est proposé à
l'autorité, sans ouverture de 5.1E.
