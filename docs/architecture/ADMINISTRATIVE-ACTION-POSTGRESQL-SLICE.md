# AdministrativeAction PostgreSQL Vertical Slice

## Statut

Première implémentation PostgreSQL certifiée du projet, limitée à `AdministrationAudit / AdministrativeAction`. Certification obtenue sur PostgreSQL 18.4 réel avec PHP 8.5.8.

## Inventaire de l’Aggregate

- Root : `AdministrativeAction`, identité `AdministrativeActionId`, version entière portée par le Domain.
- État : statut, ActionType, auteur, cible, règle quatre yeux, dernière date de changement et raison facultative.
- Enfants : Approval facultative (`ApprovalId`, approbateur, date), Decision facultative (`DecisionId`, outcome, décideur, raison, date), AuditEntry ordonnées et append-only.
- États terminaux : Approved et Rejected. Recorded est terminal pour une action sans quatre yeux.
- Reconstruction officielle : `AdministrativeAction::reconstitute`.
- Événements : recorded, approved, rejected, four-eyes satisfied et audit-entry recorded, avec métadonnées version/index. Ils ne sont pas persistés dans cette tranche.
- Observabilité ajoutée strictement pour le mapping : `requiresFourEyes()` et `lastChangedAt()`.

## Snapshot et mapping

Le snapshot Infrastructure est composé de quatre types explicites : Root, Approval, Decision et AuditEntry. Chaque Value Object est converti explicitement. Les dates sont normalisées avec microsecondes et offset ; statut et outcome utilisent leurs enums ; l’ordre des AuditEntries doit être strictement 1..n.

Le mapper refuse les dates, identités, statuts, outcomes, formes d’ActionType, versions, historiques et combinaisons d’état incohérents. ActionType reste volontairement un concept ouvert dans le Domain : le mapper refuse une forme invalide mais n’invente pas de catalogue fermé absent du modèle métier.

La reconstruction appelle uniquement `reconstitute` et vérifie que `releaseEvents()` est vide.

## Modèle physique

Ownership exclusif via le schéma `administration_audit` :

- `administrative_actions` : Root, version et optimistic locking ;
- `administrative_action_approvals` : zéro ou une Approval par Root ;
- `administrative_action_decisions` : zéro ou une Decision par Root ;
- `administrative_action_audit_entries` : preuves ordonnées par clé `(action_id, sequence)`.

Les clés étrangères utilisent `ON DELETE RESTRICT`. Aucun modèle partagé, JSON opaque, sérialisation PHP ou relation inter-module n’existe.

## Repository et transaction

Le Repository satisfait exactement `AdministrativeActionRegistry` et dépend de PDO, du mapper et d’une frontière transactionnelle locale. `add` réserve la clé primaire et écrit Root/enfants atomiquement. `save` met à jour le Root seulement si sa version durable égale `expectedVersion`; la version candidate doit être strictement supérieure et n’est jamais incrémentée par Infrastructure.

Après acquisition du Root, les preuves existantes sont comparées au préfixe du snapshot. Elles ne peuvent être ni réécrites ni retirées ; seules les nouvelles preuves sont insérées. Toute erreur annule la transaction. Le niveau attendu est Read Committed, réglage PostgreSQL par défaut de la tranche.

Conflits : violation de clé primaire à l’ajout → `AdministrativeActionIdentityConflict`; absence/version périmée → `ConcurrentAdministrativeActionModification`; donnée ou écriture incohérente → `PersistentAdministrativeActionIntegrity`, sans SQL ni détail de connexion.

Les résultats sont détachés car chaque lecture reconstruit un nouvel Aggregate. Le Repository ne libère jamais les événements de l’instance appelante.

## Outbox différée

La future Unit of Work collectera les événements de l’instance appelante après décision métier, puis écrira état, réservations et Outbox dans la même transaction. Cette tranche locale ne publie et ne persiste aucun événement : implémenter une Outbox partielle ici contredirait la frontière globale encore différée.

## Tests

- mêmes 19 scénarios contractuels via un harness PostgreSQL minimal ;
- 15 tests de mapping couvrant états, enfants, dates, ordre, versions et refus ;
- intégration réelle : commit/rollback add/save, FK enfants, versions et contraintes ;
- concurrence réelle : deux processus PHP, deux connexions, lecture avant barrière commune, puis add/save simultanés et un seul gagnant.

Les tests PostgreSQL utilisent `APPART_TEST_PG_DSN`, `APPART_TEST_PG_USER` et `APPART_TEST_PG_PASSWORD`, vérifient `server_version` 18.x et échouent explicitement sans environnement. Aucun fallback SQLite n’existe.

## Limites

- aucune Outbox, aucun Dispatcher et aucune Unit of Work globale ;
- aucun autre Registry persisté ;
- aucune suppression physique ;
- certification réelle : 27/27 tests, 94 assertions, dernière exécution certifiée en 2,591 s ; 19 contrats partagés Fake/PostgreSQL, 6 intégrations/contraintes/rollback, 2 concurrences à deux processus et deux connexions indépendantes ;
- courses `add` identique et `save` au même `expectedVersion` : un seul gagnant dans chaque cas, sans mutation partielle ;
- suite complète 606/606 (12 973 assertions), Architecture 23/23 (11 439 assertions), Pint PASS, Larastan zéro erreur et `composer quality` PASS.

La commande reproductible est `composer test:postgresql`. Elle charge `phpunit.postgresql.xml`, exige `APPART_TEST_PG_*`, passe par `PostgreSqlTestEnvironment`, applique `001_administrative_action.sql`, puis exécute contrats, intégration et concurrence. `pdo_pgsql` et `pgsql` doivent être actifs ; l’absence des variables, de PostgreSQL 18.x ou des extensions provoque un échec explicite. Aucun fallback SQLite et aucun mot de passe enregistré dans ce document.
