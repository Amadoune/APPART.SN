# Non-destruction Evidence

La garde de collision est démontrée sur la connexion réelle. En revanche, la preuve terminale par sentinelle entre deux bases ne peut pas encore être exécutée : `appart_rebuild` n'existe pas et le rôle local `appart_test` ne possède pas le privilège `CREATEDB`.

Tentative autorisée : `CREATE DATABASE appart_rebuild OWNER appart_test` → `permission denied to create database`. Le compte administrateur PostgreSQL exige un credential non disponible.

La configuration `.env` n'a donc pas été basculée vers une base inexistante. Aucune sentinelle et aucune donnée RC2 n'ont été créées ou reconstruites.

## Reopening 01 — préflight administratif

Le préflight du 15 août 2026 confirme :

- PostgreSQL local `18.4` opérationnel ;
- `appart_test` présente ;
- `appart_rebuild` absente ;
- rôle `appart_test` présent avec `rolcreatedb=false` ;
- aucune variable de credential administrateur disponible pour la campagne.

La création administrative n'a pas été tentée avec un credential deviné. Aucun changement de `pg_hba.conf`, password, rôle ou privilège n'a été effectué. Par conséquent, aucune sentinelle A/B et aucun reset supplémentaire n'ont été exécutés.

## Reopening 02 — terminal fail-closed

La recherche des moyens d'administration déjà configurés confirme que pgAdmin connaît le rôle `postgres`, mais ne conserve aucune authentification (`save_password` null). Aucun `pgpass`, credential Windows ou environnement administratif n'est disponible.

La campagne s'arrête avant création de base conformément au cas B. Aucun accès n'a été contourné, aucun secret n'a été lu, aucun privilège n'a été élargi et aucune opération destructive n'a été exécutée.

## Reopening 03 — preuve terminale A/B

L'opérateur a créé `appart_rebuild` avec owner `appart_test`, UTF8 et `template0`. La qualification confirme les deux bases présentes et `appart_test` toujours sans CREATEDB/superuser.

Une sentinelle non métier `public.rc2_database_isolation_sentinel` a été créée dans DB A. Son fingerprint avant reset est :

`c00511f2a65b9dcc89fe69fd7cd2186d097f257a081ad9615e98b8cfc18b9d87`.

Après migrations, resets et campagnes PostgreSQL sur DB B, DB A retourne exactement la même ligne et le même fingerprint. Le nouveau draft Resume possède en outre un fingerprint autoritatif stable de 8 lignes :

`84e240ea2048ca9782c312abc57916a7` avant Resume, après deux GET Resume, puis après une nouvelle campagne destructive sur DB B.

Cette preuve ferme directement le défaut historique de destruction croisée.
