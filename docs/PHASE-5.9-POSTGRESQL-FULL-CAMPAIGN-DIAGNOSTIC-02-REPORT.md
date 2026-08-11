# PostgreSQL Full Campaign Diagnostic 02

Statut : `GO CERTIFIÉ — FERMÉ — GELÉ — HISTORICAL_ONLY`.

Objectif exclusif : classifier, sans correction source, la campagne PostgreSQL Evidence 08 restée non terminale après 5 208 secondes.

Le diagnostic part d'un clone R5 propre dédié, capture la progression PHPUnit, les processus et l'état PostgreSQL, puis distingue environnement, infrastructure de test, base de données et éventuel défaut source démontré.

Evidence 09 est identifiée et non ouverte. R5 reste immuable ; aucune R6 n'est ouverte ou matérialisée.

## Protocole

- clone dédié du tag R5, HEAD exact `9801d9ed30ea3a5fa412708cd022d16bc84e472c` ;
- dépendances restaurées dans ce clone ;
- commande PHPUnit complète avec `--debug` ;
- stdout/stderr redirigés vers des fichiers temporaires non versionnés ;
- fenêtre d'exécution : deux heures ;
- sondes périodiques des processus, de `pg_stat_activity`, `pg_blocking_pids` et `pg_locks`.

Commande :

`C:\laragon\bin\php\php-8.5.8-nts-Win32-vs17-x64\php.exe vendor/bin/phpunit --configuration phpunit.postgresql.xml --debug`

## Résultat terminal

- PHPUnit : PASS ;
- tests : 763/763 ;
- assertions : 3 623 ;
- exit code PHPUnit : 0 ;
- durée PHPUnit : 880,117 secondes ;
- durée de l'enveloppe instrumentée : 889,18 secondes (14 min 49 s) ;
- stderr : vide ;
- processus PHP résiduel : aucun ;
- clone : propre après exécution.

Premier test observé : `PostgreSqlAccountStatusHistoricalAccountCompatibilityTest::test_bootstrap_reads_the_certified_historical_source_and_keeps_versions_separate` lors de la première lecture de trace. Dernier test : `PostgreSqlSecurityComplianceRuntimeTest::test_runtime_reports_the_real_owner_source_as_technically_available`.

Progression échantillonnée : AccountStatus à 02:41, LeadLifecycle à 02:44, ListingPublicationWorkflow à 02:46, ModerationQueueIdempotence à 02:48, PropertyLifecycleWorkflow à 02:51, PublicProjectionStore à 02:53 et SecurityCompliance à 02:55. Aucun groupe pathologiquement immobilisé n'est observé.

## PostgreSQL et concurrence

- requêtes actives observées : principalement `TRUNCATE` de nettoyage ;
- `pg_blocking_pids` : tableaux vides à chaque sonde ;
- verrous non accordés : 0 ;
- attente `FOR UPDATE`, `SKIP LOCKED` ou advisory lock : aucune observée ;
- session idle observée : aucune transaction ouverte (`xact_start` nul), aucun bloqueur ;
- fin de campagne : aucune transaction de test active, aucun processus PHP résiduel.

## Cause racine

Classification : `ENVIRONMENT / PROCESS EXECUTION`.

Evidence 08 utilisait l'exécution directe avec capture par l'enveloppe et n'a produit aucune sortie intermédiaire avant le timeout ; un processus PHP est resté actif après l'expiration. Le même R5, le même PHP et le même PostgreSQL terminent normalement lorsque PHPUnit est lancé sous un processus explicitement supervisé avec stdout/stderr redirigés hors dépôt. La campagne reste constamment active, sans lock ni attente serveur, et termine dans la durée historique normale.

La divergence est donc imputable à la supervision/capture du processus Evidence 08 ou à une condition locale transitoire de cette enveloppe. Aucun défaut de test, de base ou de source R5 n'est reproduit. Aucune modification R5 et aucune R6 ne sont nécessaires.

## Prérequis futur

Pour une future Evidence, lancer PHPUnit avec une supervision explicite du processus, capturer stdout/stderr dans des fichiers non versionnés, conserver une fenêtre supérieure à la durée historique et vérifier l'absence de processus résiduel. Cette preuve diagnostique ne vaut pas PASS Evidence 09.

## Verdict

GO CERTIFIÉ — FERMÉ — GELÉ — HISTORICAL_ONLY
