# Phase 5.0B — Quality Baseline

## 1. Environnement de référence

| Élément | Valeur |
|---|---|
| Date | 26 juillet 2026 |
| PHP exécuté | 8.5.8 CLI NTS |
| Framework déclaré | Laravel 13 |
| PHPUnit déclaré | 12.5.12 |
| PostgreSQL cible | 18.x réel, aucun fallback SQLite |
| Runtime Health | 58 requirements |

Sous Laragon Windows, PHP et Composer peuvent nécessiter :

```powershell
$env:Path='C:\laragon\bin\php\php-8.5.8-nts-Win32-vs17-x64;C:\laragon\bin\composer;'+$env:Path
```

## 2. Baseline certifiée

```text
Architecture : 592 / 592 tests, 44 523 assertions
Unit         : 1 891 / 1 891 tests, 6 609 assertions
Feature      : 249 / 249 tests, 1 339 assertions
Foundation   : 1 / 1 test, 4 assertions
Total        : 2 733 / 2 733 tests, 52 475 assertions
Runtime      : Healthy, 58 capacités
PHPStan      : 0 erreur
Pint         : PASS
diff check   : PASS
```

Les quatre suites ont été réexécutées séparément en 5.0B et reproduisent
exactement la baseline finale 4.9L. `composer quality` a confirmé Pint et
PHPStan avant d'entrer dans la suite de tests ; la segmentation évite de
confondre la limite interactive de 60 secondes avec un échec.

## 3. Gate PostgreSQL

La campagne PostgreSQL est indépendante de la suite applicative. La dernière
preuve globale enregistrée avant 4.9 est `555 / 555`, 2 355 assertions ; la
preuve ciblée finale Account Status + Outbox est `52 / 52`, 391 assertions.

Commande normative :

```bash
composer test:postgresql
```

Préconditions :

- instance PostgreSQL 18.x joignable ;
- base de test dédiée et credentials `phpunit.postgresql.xml`/environnement ;
- extensions PDO PostgreSQL actives ;
- droit de créer/appliquer/rollback les schémas de test ;
- aucune base de production ou partagée.

L'exécution globale tentée dans la fenêtre 5.0B a dépassé 60 secondes sans
produire de verdict final. Elle est enregistrée comme **NON CONCLUSIVE**, pas
comme échec ni PASS. 5.0B étant documentaire et sans changement SQL, la
baseline PostgreSQL certifiée est conservée ; toute phase modifiant persistence,
Runtime ou Outbox devra fournir une nouvelle exécution complète.

## 4. Commandes reproductibles

```bash
composer quality
composer test:architecture
composer test:postgresql
composer security:audit
git diff --check
git status --short
```

Pour diagnostiquer sans remplacer `composer test` :

```bash
php artisan test --testsuite=Architecture
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
php artisan test --testsuite=Foundation
```

Runtime Health est vérifié dans les tests Feature de composition, notamment
Account Status, qui exigent `Healthy`, 58 capacités et zéro diagnostic.

## 5. Politique de comparaison

- zéro test en moins sans justification et décision ;
- zéro assertion en moins sans analyse ;
- zéro erreur PHPStan ;
- Pint et `git diff --check` obligatoirement verts ;
- Runtime Health sans composant absent/incompatible ;
- aucun skip masqué, fallback ou timeout compté comme PASS ;
- PostgreSQL obligatoire pour toute modification de persistence, transaction,
  migration, inbox, Outbox ou repository ;
- `git status --short` sert à attribuer le diff, pas à exiger un worktree
  globalement propre lorsqu'il contient des changements préexistants.

## 6. Baseline documentaire 5.0B

Les seuls fichiers autorisés sont Markdown. La vérification finale doit
confirmer qu'aucun `.php`, `.sql`, configuration, route, provider, migration ou
test n'a été modifié par 5.0B.
