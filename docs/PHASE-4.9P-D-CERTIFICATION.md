# Phase 4.9P-D — Historical Account Runtime Composition Certification

## Livrables

- singleton `AccountPersistenceMapper`;
- singleton `PostgreSqlAccountRepository`;
- alias unique `AccountRegistry`;
- catalogue Runtime Health gelé et non muté à 55 capacités;
- tests Feature et Architecture;
- documentation de composition.

## Matrice GO

| Critère | Résultat |
|---|---|
| binding AccountRegistry unique | SATISFAIT |
| binding Repository unique | SATISFAIT |
| même instance port / implémentation | SATISFAIT |
| mapper singleton | SATISFAIT |
| résolution paresseuse | SATISFAIT |
| PDO partagé | SATISFAIT |
| Runtime Health honnête | SATISFAIT |
| aucune lecture ou transaction de sonde | SATISFAIT |
| aucun Runtime Account Status | SATISFAIT |
| fondations certifiées inchangées | SATISFAIT |

## Validation ciblée

```text
Feature / Architecture
6 tests
34 assertions
PASS
```

Les campagnes complètes exécutées sont enregistrées ci-dessous.

## Validation complète

```text
Architecture
564 / 564 tests
43 448 assertions
PASS

Suite complète
2 657 / 2 657 tests
51 172 assertions
PASS

PostgreSQL
555 / 555 tests
2 355 assertions
PASS

PHPStan
0 erreur

Pint
PASS

Runtime Health
Healthy — 55 capacités (catalogue gelé inchangé)
```

## Verdict certifié

```text
4.9P-D
→ GO CERTIFIÉ
→ FERMÉ

4.9P Final Certification
→ OUVERTE

Recertification 4.9C
→ FERMÉE

4.9D
→ RESTE SUSPENDU
```
