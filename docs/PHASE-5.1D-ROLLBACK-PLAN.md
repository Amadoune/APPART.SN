# Phase 5.1D — Rollback Plan

## Préconditions

Le rollback n'est accepté que si :

- le run demandé est l'`active_run_id` ;
- l'autorité courante est `Profile` ;
- chaque Profile est encore à la version seed manifestée ;
- les deux Claim IDs manifestés existent encore à la version seed.

Cette vérification interdit d'effacer une mutation post-cutover. Dans ce cas le
rollback est contrôlé et rejeté ; aucun état partiel n'est produit.

## Transaction

Dans une transaction unique :

1. verrouiller le manifest ;
2. supprimer uniquement les claims seedés inchangés ;
3. supprimer uniquement les profiles seedés inchangés ;
4. basculer `Profile → Historical` par compare-and-set ;
5. marquer le run `RolledBack`.

Snapshot V1 et toutes les tables historiques restent intacts. Les manifests et
rapports sont conservés comme preuve. Le rollback ne supprime ni Historical
Account ni Snapshot V1 et ne réalise aucune anonymisation.

## Après rollback

Il n'existe plus de double autorité : Historical redevient immédiatement
canonique. Un nouveau cutover exige un nouveau `run_id`, une nouvelle
préqualification complète et un checksum propre.
