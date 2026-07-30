# PostgreSQL Repository Checklist

Checklist obligatoire, datée et renseignée pour chaque Aggregate. Une case non applicable exige une justification métier explicite ; elle ne peut pas être supprimée.

## Autorisation et frontières

- [ ] GO explicite limité à l’Aggregate.
- [ ] Port Registry et Reservation Strategy audités.
- [ ] Aucun Repository, mapper, snapshot ou transaction générique/partagé.
- [ ] Aucun SQL/PDO hors Infrastructure du module.
- [ ] Aucune règle métier déplacée vers Infrastructure.
- [ ] Aucun changement Domain motivé uniquement par la persistance.

## Contrat et Fake

- [ ] Contrat partagé complet et déterministe.
- [ ] Harness abstrait backend-agnostique.
- [ ] Contrats Fake entièrement verts.
- [ ] Détachement des lectures et snapshots internes garanti.
- [ ] Événements de l’appelant conservés après succès et échec.
- [ ] `releaseEvents()` vide après reconstruction et aucun rejeu.
- [ ] Réservations, réutilisation et rollback conformes à la stratégie normative.

## Snapshot et mapping

- [ ] Snapshot Root `final readonly`, complet et typé.
- [ ] Snapshots enfants uniquement si le modèle le requiert.
- [ ] Mapper explicite aller/retour sans PDO ni SQL.
- [ ] Round trip de chaque état pertinent vérifié.
- [ ] Identités, dates, ordre, preuves, enfants et version préservés exactement.
- [ ] Snapshots inconnus, incomplets, incohérents ou dupliqués refusés.
- [ ] Reconstruction officielle sans événements résiduels.

## Migration et contraintes

- [ ] Migration propriétaire, ordonnée et limitée au module.
- [ ] Schéma/table/contraintes conformes aux conventions de nommage.
- [ ] Clés primaires, étrangères, uniques, checks et nullabilité testés.
- [ ] Réservations atomiques matérialisées par des contraintes durables.
- [ ] Aucun identifiant ni secret dans le DSN versionné.

## Repository et transaction

- [ ] `find`, `add` et `save(expectedVersion)` respectent exactement le port.
- [ ] Repository sans règle métier et sans incrément de version.
- [ ] Requêtes préparées et paramètres explicites.
- [ ] Transaction locale injectable couvrant Root, enfants et réservations.
- [ ] Rollback `add` total prouvé avant commit.
- [ ] Rollback `save` total prouvé avant commit.
- [ ] Historique append-only et préfixe durable contrôlés.
- [ ] Erreurs driver traduites vers les exceptions publiques attendues.
- [ ] Aucun SQL, table, DSN ou secret exposé par une exception publique.

## Optimistic locking et concurrence

- [ ] Version candidate cohérente et strictement progressive.
- [ ] Mise à jour conditionnée par `expectedVersion` exacte.
- [ ] Root absent et version périmée produisent le conflit public.
- [ ] Deux processus et deux connexions indépendantes utilisés.
- [ ] Course `add` : exactement un gagnant.
- [ ] Course `save` au même `expectedVersion` : exactement un gagnant.
- [ ] État final complet, aucune mutation partielle.

## Validation finale

- [ ] Contrats PostgreSQL entièrement verts sur PostgreSQL 18.x réel.
- [ ] Tests mapping, contraintes, intégration et rollback verts.
- [ ] Tests de concurrence réels verts.
- [ ] Suite complète verte, sans test ignoré ou désactivé.
- [ ] Architecture verte.
- [ ] Pint vert.
- [ ] Larastan vert, zéro erreur.
- [ ] `composer quality` vert.
- [ ] Documentation et inventaires mis à jour.
- [ ] Audit secrets et `git diff --check` verts.
- [ ] GO final consigné avec nombres de tests, assertions et durée.
