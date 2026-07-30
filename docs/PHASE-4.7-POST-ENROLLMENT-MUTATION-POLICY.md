# Phase 4.7B-R2 — Post-Enrollment Mutation Policy

## Avant enrôlement

Création, ajout du motif et détails d'audit historiques restent autorisés par leurs contrats historiques.

## Après enrôlement

- aucune création, mutation de motif ou mutation d'audit autonome n'est autorisée ;
- aucune seconde opération d'enrôlement divergente n'est autorisée ;
- toute évolution de version passe par `LifecycleTransition` ;
- la transition porte la mutation historique exacte et écrit journal + miroir atomiquement ;
- les lectures de compatibilité restent autorisées ;
- les lectures Lifecycle utilisent le journal.

Cette politique garantit :

```text
historicalVersion == lifecycleVersion
```

au checkpoint et après chaque commit de transition. Aucun saut, mutation latérale ou rattrapage n'est autorisé.
