# PostgreSQL Repository Guide

## Statut

Référence officielle des futurs Repositories PostgreSQL à compter du Sprint 2.3D. La tranche certifiée `AdministrationAudit / AdministrativeAction` en est l’implémentation témoin. Ce guide standardise une méthode ; il n’autorise ni Repository supplémentaire ni abstraction générique.

## Périmètre du modèle de référence

La tranche témoin comprend un snapshot de Root et des snapshots enfants immuables, un mapper explicite bidirectionnel, une frontière transactionnelle locale, un Repository PDO, une migration propriétaire, un harness PostgreSQL réutilisant le contrat partagé, des tests de mapping et d’intégration, ainsi que deux courses réelles à connexions indépendantes.

Sont spécifiques à AdministrativeAction : `ApprovalSnapshot`, `DecisionSnapshot`, `AuditEntrySnapshot`, la règle four-eyes, l’ordre des audit entries, les quatre tables du schéma `administration_audit` et les erreurs `AdministrativeAction*`. Ils ne doivent jamais être copiés dans un autre module sans correspondance métier démontrée.

Sont réutilisables comme règles : séparation Domain/Infrastructure, snapshots typés, mapping exhaustif, reconstruction officielle, nettoyage des événements reconstruits, transaction atomique locale, verrouillage optimiste conditionnel, traduction des erreurs, rollback total, harness minimal et mêmes scénarios contractuels Fake/PostgreSQL.

## Structure obligatoire

Chaque tranche appartient à un seul module et à un seul Aggregate Root :

```text
src/Modules/<Module>/Infrastructure/Persistence/
  <Aggregate>Snapshot.php
  [<Child>Snapshot.php]
  <Aggregate>Mapper.php
  <Aggregate>Transaction.php
  Persistent<Aggregate>Integrity.php
  PostgreSql/
    PostgreSql<Aggregate>Repository.php
    PostgreSql<Aggregate>Transaction.php
    Migrations/<sequence>_<aggregate>.sql
```

Le Repository implémente directement le Registry applicatif du module. Il ne contient aucune règle métier, n’est ni générique ni partagé et ne dépend d’aucun use case.

## Snapshot

- `final readonly`, typé, sans comportement métier et local à Infrastructure.
- Représente toutes les données nécessaires à une reconstruction fidèle : Root, version, état, dates, Value Objects sérialisés et enfants ordonnés.
- Ne contient ni PDO, SQL, Aggregate mutable, événement en attente ni dépendance Laravel.
- Les collections append-only conservent identité, contenu, ordre et preuve temporelle.
- Un snapshot incomplet, incohérent ou inconnu est refusé par une erreur d’intégrité persistante ; il n’est jamais corrigé silencieusement.

## Mapper

- Un mapper explicite par Aggregate, sans mapper générique.
- `toSnapshot` observe uniquement l’API publique du Root et ne consomme pas ses événements.
- `toAggregate` utilise la méthode officielle `reconstitute`; il valide enums, dates, enfants, ordre, unicité et cohérence version/état.
- Le round trip préserve toutes les propriétés observables et produit un Aggregate détaché dont `releaseEvents()` est vide.
- La prochaine mutation après reconstruction produit exactement la version suivante.
- Le mapper ne requiert ni PDO ni connaissance des tables.

## Repository et mapping relationnel

- `find` retourne `null` pour l’absence, reconstruit un Root détaché et ne rejoue aucun événement historique.
- `add` réserve et écrit le Root et toutes ses réservations/enfants dans une transaction unique.
- `save` reçoit `expectedVersion`, refuse un candidat sans progression et effectue une écriture conditionnelle sur la version durable.
- Le Repository ne modifie jamais la version du Root et ne vide jamais les événements de l’instance appelante.
- Les écritures enfant append-only vérifient le préfixe durable avant d’ajouter uniquement le suffixe nouveau.
- Requêtes préparées et paramètres typés obligatoires. Aucun détail SQL ou de connexion ne traverse le port.
- Schéma, tables, contraintes, clés étrangères, unicités et checks appartiennent exclusivement à la migration du module.

## Transaction et rollback

La frontière transactionnelle est locale à l’Aggregate et injectable. Elle démarre, valide ou annule l’unité atomique. Toute exception avant commit provoque un rollback si la transaction est active, puis est propagée ou traduite sans perte de cause.

Après échec de `add`, aucun Root, enfant ou réservation ne devient visible. Après échec de `save`, ni version, ni état, ni nouvel enfant n’est visible. Les événements du candidat restent disponibles. Aucun appel externe n’est exécuté dans la transaction.

## Optimistic locking et concurrence

La condition de succès de `save` est l’égalité entre version durable et `expectedVersion`, avec une version candidate strictement supérieure selon le contrat du Root. Zéro ligne modifiée signifie le conflit public de concurrence. Une course avec deux connexions et deux processus indépendants doit démontrer :

- un seul gagnant pour deux `add` de la même réservation ;
- un seul gagnant pour deux `save` utilisant le même `expectedVersion` ;
- état final complet et aucune mutation partielle.

Les tests séquentiels ne remplacent pas cette preuve réelle.

## Erreurs

- Conflit de réservation/identité à l’ajout → exception publique définie par le Registry.
- Root absent ou version périmée à la sauvegarde → exception publique de concurrence.
- Donnée persistée incohérente, état inconnu ou historique invalide → `Persistent<Aggregate>Integrity` local à Infrastructure.
- Les messages publics n’exposent jamais SQL, table, DSN, utilisateur, mot de passe ou code driver.
- La traduction repose sur les catégories stables du driver et les contraintes nommées, jamais sur un texte localisé fragile.

## Tests et harness

Avant PostgreSQL, le contrat partagé doit être vert contre le Fake. La classe d’entrée backend ne redéfinit aucun scénario. Le harness PostgreSQL remplace uniquement la création du Registry, la connexion indépendante et le mécanisme déterministe d’échec si nécessaire.

Une tranche complète comporte :

1. tests unitaires aller/retour et snapshots invalides du mapper ;
2. mêmes scénarios contractuels contre Fake et PostgreSQL ;
3. intégration des contraintes, enfants et rollback `add`/`save` ;
4. concurrence réelle `add` et `save` ;
5. architecture, Pint, Larastan, suite complète et `composer quality`.

Les fixtures utilisent identités et dates fixes. Chaque scénario repart d’un Registry et d’un schéma isolés. Aucun fallback SQLite, skip backend ou ordre de tests implicite n’est permis.

## Porte d’adoption

Un nouveau Repository nécessite une autorisation limitée à son Aggregate, la checklist complète, une exécution PostgreSQL 18.x réelle et une mise à jour documentaire. La conformité à ce guide ne constitue jamais un GO général pour les autres modules, l’Outbox, le Dispatcher ou une Unit of Work globale.
