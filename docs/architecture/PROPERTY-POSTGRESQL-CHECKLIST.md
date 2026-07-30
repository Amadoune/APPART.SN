# Property PostgreSQL Checklist — 2026-07-18

## Autorisation et frontières

- [x] GO limité à Property et fondation contractuelle auditée.
- [x] Accesseur Domain minimal autorisé, en lecture seule, sans invariant modifié.
- [x] Aucun composant générique/partagé; SQL/PDO confinés à Infrastructure.

## Mapping et contrats

- [x] Snapshots Property/Address `final readonly`, complets et typés.
- [x] Mapper explicite, reconstruction officielle et PropertyTypePolicy non dupliquée.
- [x] Identités, référence, options, Address, date/offset, état et version fidèles.
- [x] Reconstruction sans événements et événements appelants conservés.
- [x] Contrats Fake et PostgreSQL réutilisés sans duplication.

## Migration et Repository

- [x] Migration propriétaire `003_property.sql` exécutée après 001 et 002.
- [x] Schéma `real_estate_catalog`, Root, réservation de référence et Address uniquement.
- [x] PropertyId et PropertyReference réservés définitivement.
- [x] Contraintes structurantes, FK restrictives et index testés.
- [x] Transaction locale injectable; rollback add/save total.
- [x] Save conditionné par version exacte et version gérée par Domain.
- [x] Exceptions publiques distinctes sans fuite technique.

## Concurrence et qualité

- [x] Deux processus/connexions : un gagnant sur PropertyId.
- [x] Deux processus/connexions : un gagnant sur PropertyReference.
- [x] Deux processus/connexions : un gagnant sur expectedVersion.
- [x] AdministrativeAction et Listing PostgreSQL restent verts.
- [x] Suite complète, Architecture, Pint, Larastan et quality verts.
- [x] Diff, secrets, périmètre et nombre exact de Repositories audités.
