# RC2-R5-F22-MIGRATION-058-ROLLBACK-CORRECTIVE-AMENDMENT-01

## Statut

Amendement correctif ouvert. Le candidat technique est matérialisé sur une branche issue directement de `appart-sn-release-candidate-rc2-r5`. Il n'est pas un successor certifié et ne doit pas être déployé tant que toutes les qualifications obligatoires ne sont pas PASS.

Verdict maintenu : **NO GO — F-22 / MIGRATION 058 ROLLBACK DEFECT**.

## Autorité et immutabilité

- tag source immuable : `appart-sn-release-candidate-rc2-r5` ;
- commit source : `0067a77423c2ff16b02f35d85301d45024742a77` ;
- tree source : `6f258ddacc7f5cf2701c8e98ffaccb7e0c3d14f7` ;
- aucun commit, tag ou artefact R5 n'est modifié ;
- aucune migration 101 n'est créée ;
- aucun UP SQL, ni 059, ni 060 n'est modifié ;
- aucune opération de production et aucun accès à `appart_production` ne sont autorisés par cet amendement.

## Cause racine confirmée

Le UP 058 crée le schéma `media_ingestion`, huit tables et trois index. Son DOWN R5 ne contient que `DROP SCHEMA IF EXISTS media_ingestion;`. PostgreSQL refuse donc correctement la suppression du schéma non vide sans `CASCADE`.

Les huit tables owner-scoped créées par 058 sont :

1. `media_ingestion.uploads` ;
2. `media_ingestion.assets` ;
3. `media_ingestion.processing` ;
4. `media_ingestion.quotas` ;
5. `media_ingestion.upload_intents` ;
6. `media_ingestion.asset_intents` ;
7. `media_ingestion.processing_intents` ;
8. `media_ingestion.quota_intents`.

Les trois index explicites sont `media_ingestion_upload_state_idx`, `media_ingestion_asset_state_idx` et `media_ingestion_processing_state_idx`. Les contraintes PRIMARY KEY et CHECK ainsi que leurs index implicites sont possédés par les tables. Le DDL 058 ne contient aucune clé étrangère, aucun `REFERENCES`, aucune vue, fonction, séquence explicite ou dépendance inter-schéma. La suppression explicite des huit tables supprime donc leurs index et contraintes internes sans `CASCADE`.

Le UP/DOWN 059 est borné à `media.media_attachment_intents`. Le UP 060 crée `media_ingestion.event_outbox_messages` et `media_ingestion.event_outbox_deliveries`; son DOWN les supprime explicitement. Aucun des deux fichiers ne crée de dépendance vers une table de 058. L'ordre normatif demeure UP 058→059→060 et DOWN 060→059→058.

## Frontière corrective minimale

Le seul SQL modifié est `058_media_ingestion.down.sql`. Il supprime explicitement, dans l'ordre inverse de création, les quatre tables d'intents puis les quatre tables d'agrégats, avant `DROP SCHEMA IF EXISTS media_ingestion;`. `DROP SCHEMA ... CASCADE` reste interdit.

Trois tests sont ajoutés :

- un test PostgreSQL réel 058 DOWN/UP vérifie les huit tables, la disparition du schéma, la restauration exacte des relations/contraintes owner-scoped et l'invariance de toutes les relations hors `media_ingestion` ;
- un test PostgreSQL réel vérifie UP 058→059→060 puis DOWN 060→059→058 ;
- un test Architecture verrouille statiquement les huit `DROP TABLE`, l'absence de `CASCADE` et l'absence d'objets 059/060 dans le DOWN 058.

## Qualification du candidat

État au 3 septembre 2026 :

| Contrôle | Résultat | Preuve |
|---|---:|---|
| Architecture ciblée | PASS | 4 tests, 99 assertions |
| Unit Media Ingestion pertinentes | PASS | 11 tests, 36 assertions |
| Feature Media Ingestion pertinentes | PASS | 4 tests, 40 assertions |
| PostgreSQL ciblé | BLOCKED | connexion refusée avant opération : `APPART_TEST_PG_PASSWORD` absent |
| PostgreSQL complet | NON EXÉCUTÉ | dépend du même secret de base de test |
| Architecture complète | FAIL baseline R5 | 970/987 tests passent ; 15 failures et 2 errors hors frontière corrective |
| Pint complet | FAIL baseline R5 | trois fichiers R5 hors delta : `bootstrap/providers.php`, `routes/web.php`, `tests/PostgreSQL/MediaIngestionRuntime/PostgreSqlMediaIngestionRuntimeTest.php` |
| PHPStan complet | PASS | exécution séquentielle `--debug`, aucune erreur |
| `git diff --check` | PASS | aucune erreur |

Les défauts de baseline hors frontière ne sont pas corrigés par cet amendement. Les qualifications incomplètes ou en échec interdisent la matérialisation d'un tag successor.

## Condition de successor

Un successor immuable de R5 ne pourra être tagué comme candidat certifié qu'après :

1. injection autorisée du secret `APPART_TEST_PG_PASSWORD` pour `appart_test` ;
2. PASS des tests PostgreSQL ciblés et complets sur PostgreSQL 18.x ;
3. résolution d'autorité des échecs Architecture/Pint de la baseline R5, sans élargissement silencieux du présent amendement ;
4. PASS PHPStan et `git diff --check` ;
5. publication des commit/tree/tag exacts et du delta exact R5→successor.

Après seulement cette matérialisation, le successor devra subir un nouveau rehearsal global jetable UP 001→100 puis DOWN 100→013. Aucun déploiement production n'est inclus.
