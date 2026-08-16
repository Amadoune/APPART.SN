# Migration Strategy

Migration additive suivante après la baseline actuelle 097, avec numéro exact réservé au moment de l'implémentation :

1. garantir le schéma `geography` ;
2. créer `geography.places` et contraintes/index ;
3. créer `geography.place_aliases` ;
4. aucun INSERT, backfill ou import.

Les données lifecycle, outbox/inbox, `public_geography`, tests et démonstrations ne sont pas promues. Aucun Aggregate complet autoritatif existant n'a été identifié.

Rollback dédié : supprimer aliases puis places. Il ne touche aucune table historique existante. L'implémentation doit vérifier l'ordre global des migrations avant d'attribuer son numéro afin d'éviter une collision avec le worktree courant.
