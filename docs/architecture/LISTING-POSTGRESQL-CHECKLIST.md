# Listing PostgreSQL Checklist — 2026-07-17

## Autorisation et frontières

- [x] GO limité à Listing et port/Reservation Strategy audités.
- [x] Aucun composant générique ou partagé ; SQL/PDO confinés à Infrastructure.
- [x] Domain et Fake inchangés ; seule l’extensibilité du harness de test a été alignée sur le modèle certifié.

## Contrat, snapshot et mapping

- [x] 14 contrats partagés verts contre Fake et PostgreSQL, sans duplication.
- [x] Lectures détachées, événements appelants conservés et reconstruction sans événements.
- [x] Snapshots Root/révision `final readonly`, complets et typés.
- [x] Round trips, états, identités, version, dates/offsets et ordre préservés.
- [x] Snapshots inconnus, incomplets, dupliqués ou incohérents refusés.

## Migration, Repository et transaction

- [x] Migration propriétaire `002_listing.sql`, schéma `listing_lifecycle` et deux tables seulement.
- [x] PK permanente, FK restrictive, unicité locale, séquence, checks et index testés.
- [x] `find`, `add`, `save(expectedVersion)` conformes au port.
- [x] Transaction locale injectable et rollbacks `add`/`save` totaux.
- [x] Historique append-only et préfixe durable contrôlés.
- [x] Erreurs traduites sans fuite technique ou secret.

## Optimistic locking et concurrence

- [x] Version gérée exclusivement par le Domain et mise à jour conditionnelle.
- [x] Root absent et version périmée produisent le conflit public.
- [x] Deux processus/connexions : exactement un gagnant `add`.
- [x] Deux processus/connexions : exactement un gagnant `save`.
- [x] État final complet, aucune mutation ou révision partielle.

## Validation finale

- [x] Mapper, contrats Fake/PostgreSQL, intégration, rollback et concurrence verts.
- [x] AdministrativeAction PostgreSQL reste vert.
- [x] Suite complète, Architecture, Pint, Larastan et `composer quality` verts.
- [x] `git diff --check`, audit secrets et audit exact de deux Repositories verts.
- [x] Documentation de la tranche et gouvernance mises à jour.
