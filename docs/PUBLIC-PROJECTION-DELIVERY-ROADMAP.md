# Public Projection Delivery — roadmap d'implémentation

ADR-1009 est la décision normative. Chaque étape possède un GO indépendant et ne doit pas anticiper la suivante.

## 1. Delivery Contract Foundation

**Statut : GO — certifié le 19 juillet 2026.**

L'enveloppe immutable, les identités spécialisées, la clé canonique encodée, l'ordre version/index, les cinq payloads DTO, le catalogue fermé, les ports et le Fake contractuel sont livrés sans infrastructure.

- **Objectif :** définir enveloppe immutable, event catalog, statuts, idempotency key, ordre et contrats producteur/consommateur.
- **Périmètre :** contrats PHP et Fakes uniquement.
- **Interdictions :** SQL, Laravel, worker, repository, transport.
- **Dépendances :** ADR-1009 Accepted.
- **Tests :** stabilité de clé, ordre version/index, compatibilité payload, données interdites.
- **GO :** chaque champ et ownership sont exécutables sans infrastructure.

## 2. Transactional Outbox Contract

**Statut : GO — certifié le 19 juillet 2026.**

Le Record immutable, les ports Writer/Reader/Claim/Cursor, les contrats lease/retry/quarantaine/replay et les quatre Fakes sont livrés sans stockage ni runtime.

- **Objectif :** définir writer local, claim, accusé par consommateur, retry, blocage et quarantaine.
- **Périmètre :** protocoles et harness contractuel en mémoire.
- **Interdictions :** PostgreSQL, dispatcher runtime, queue.
- **Dépendances :** étape 1.
- **Tests :** doublon, trou, claim concurrent, bail expiré, redelivery, quarantaine, `BlockedBySourceReadiness`.
- **GO :** at-least-once et absence de perte sont démontrables sur le contrat.

## 3. PostgreSQL Outbox Foundation

**Statut : GO — certifié le 19 juillet 2026.**

La suite PostgreSQL réelle passe 95/95 tests et 438 assertions. L'atomicité Aggregate + Outbox, le rollback commun, le refus d'imbrication, les claims, leases, retries et quarantaines sont certifiés.

- **Objectif :** créer une Outbox propriétaire dans chaque module source autorisé et la frontière transactionnelle locale.
- **Périmètre :** migrations, mapper, adaptateurs, transaction partagée sur une connexion.
- **Interdictions :** Outbox centrale, broker, modification silencieuse des repositories.
- **Dépendances :** étapes 1–2 ; stratégie de révision Geography/Media si ces modules deviennent producteurs.
- **Tests :** PostgreSQL réel, commit/rollback commun, contrainte idempotente, absence d'imbrication, concurrence et isolation.
- **GO :** Aggregate et message sont visibles ensemble ou pas du tout ; les quatre repositories restent inchangés. Sinon arrêt et nouvelle certification.

## 4. Worker / Dispatcher Foundation

**Statut : GO — certifié le 19 juillet 2026.**

Le passage borné, le registre explicite, l'interprétation exhaustive, la reprise de lease expirée et la redelivery idempotente sont certifiés. La suite PostgreSQL cumulée passe 99/99 tests et 451 assertions, dont la concurrence Worker à deux processus sans double effet et le progrès parallèle d'Aggregates distincts.

- **Objectif :** livrer après commit selon le catalogue explicite.
- **Périmètre :** relay, claiming borné, dispatcher et marqueur consommateur.
- **Interdictions :** règles métier, découverte magique, exactly-once déclaré, dispatch avant commit.
- **Dépendances :** étape 3.
- **Tests :** ordre par Aggregate, parallélisme inter-Aggregates, crash avant/après effet, bail expiré, version inconnue.
- **GO :** redelivery sûre, aucun message perdu, lag observable.

## 5. Retry and Quarantine

**Statut : GO — certifié le 19 juillet 2026.**

La policy déterministe, les backoffs fixe et exponentiel plafonné, les dispositions de retry, les cinq scopes de replay, l'autorisation de reprise, les règles de sortie de quarantaine et le snapshot d'observabilité sont certifiés sans infrastructure runtime. Les tests ciblés passent 9/9 avec 35 assertions ; la suite complète passe 908/908 avec 21 637 assertions.

- **Objectif :** classifier les erreurs et exploiter retries, backoff, quarantaine et reprise auditée.
- **Périmètre :** politiques techniques configurables et outils de reprise ciblée.
- **Interdictions :** boucle infinie, suppression d'échec, payload dans les logs.
- **Dépendances :** étape 4.
- **Tests :** transitoire/permanent, seuil, jitter borné, reprise message/Aggregate/module, readiness bloquée.
- **GO :** toute erreur possède un état durable et une procédure de reprise.

## 6. Projection Updater Integration

**Statut : GO — certifié le 19 juillet 2026.**

Le Consumer applicatif, le Resolver de sources/readiness, l'adaptateur exclusif vers `PublicListingProjectionUpdater` et leur enregistrement explicite dans le Worker sont certifiés. Geography et Public Media sans révision stable bloquent avant l'Updater. PostgreSQL passe 100/100 tests et 454 assertions ; la suite complète passe 929/929 tests et 21 798 assertions.

- **Objectif :** créer le consommateur qui résout les sources et appelle l'Updater 3.6B.
- **Périmètre :** adaptation message vers reconstruction ciblée, marqueur idempotent.
- **Interdictions :** reconstruction dans le handler, règle SEO/Search, scan global, accès HTTP.
- **Dépendances :** étapes 1–5 et révisions stables Public Geography/Public Media.
- **Tests :** Applied/AlreadyApplied, obsolete/divergent, canonical replacement requis, génération incorrecte, indisponibilité Store.
- **GO :** le consommateur délègue uniquement et traite tous les résultats sans perte.

## 7. Reconciliation and Replay

**Statut : GO — certifié le 19 juillet 2026.**

Le moteur applicatif borné sépare source paginée, détection, analyse, décision et planification ciblée. Les messages manquants, trous de séquence, divergences de high-watermark et blocages sont expliqués sans réparation silencieuse. Le checkpoint permet une reprise sans perte de progression. PostgreSQL reste vert à 100/100 tests et 454 assertions ; la suite complète passe 935/935 tests et 22 016 assertions.

- **Objectif :** détecter trous, blocages et divergences, puis programmer replay ou rebuild ciblé.
- **Périmètre :** curseurs, high-watermarks, batch borné, métriques.
- **Interdictions :** mécanisme principal de fraîcheur, scan HTTP, modification directe d'Aggregate.
- **Dépendances :** étape 6 et contrats de génération ADR-1008.
- **Tests :** reprise curseur, message manquant, watermark divergent, rebuild candidat, arrêt/reprise.
- **GO :** le batch répare sans masquer les défaillances événementielles.

## 8. Runtime Certification

**Statut : NO GO — prérequis restants au 19 juillet 2026.**

Le Projection Store PostgreSQL est désormais certifié par le Sprint 3.6D. Le Runtime reste arrêté tant que les sources Runtime, le lookup concret, le planner durable de replay, les révisions stables Public Geography/Public Media et les étapes Store 3.6E–3.6G applicables ne sont pas disponibles. Aucun binding Fake ou null n'est introduit.

- **Objectif :** bindings Laravel, supervision, health/readiness, SLO et exploitation réelle.
- **Périmètre :** configuration, processus relay, alertes, runbooks, purge sûre.
- **Interdictions :** fake/null binding, logique dans Service Provider, purge avant curseurs.
- **Dépendances :** étapes 1–7 et Projection Store PostgreSQL certifié.
- **Tests :** bootstrap réel, crash process, redémarrage, charge, lag, HTTP 200/404 après livraison, rollback opérationnel.
- **GO :** une mutation réelle atteint durablement la page publique, avec reprise démontrée et métriques actives.

## Portes transverses

- aucune étape ne revendique exactly-once ;
- aucun timestamp ne remplace version/index ;
- aucune donnée sensible non nécessaire dans les payloads ;
- Geography publique et Media publique ne deviennent promotables qu'avec une révision stable ;
- toute nécessité de modifier un repository certifié interrompt le sprint concerné et déclenche une nouvelle certification.
