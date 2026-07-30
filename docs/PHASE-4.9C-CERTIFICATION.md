# Phase 4.9C — Account Status Persistence Foundation Certification

## Livrables

- port `AccountStatusWorkflowStore`;
- snapshots et résultats fermés;
- `AccountStatusWorkflowMapper`;
- `PostgreSqlAccountStatusWorkflowStore`;
- migration additive et réversible 041;
- tests unitaires, PostgreSQL et d'architecture;
- documentation de mapping et rollback.

## Conformité

| Critère | Preuve |
|---|---|
| autorité unique | journal `identity_access` |
| version indépendante | séquence lifecycle depuis 0 |
| amorçage atomique | transaction, verrou advisory, test à deux processus |
| absences distinctes | `AccountMissing` / `LegacyUninitialized` |
| concurrence | `VersionConflict` contre la version lifecycle |
| corruption | checksum et `PersistenceRejected` |
| aucune double écriture | aucun appel `AccountRegistry::save()` |
| aucune double décision | aucun appel aux mutations historiques |
| rollback | down/up 041 exécutés sur PostgreSQL |
| fondations gelées | Workflow, Account, AccountRegistry et 038–040 inchangés |

## Validations

- tests ciblés Unit / Architecture : **7 / 7**, **39 assertions**;
- tests PostgreSQL ciblés finaux : **8 / 8**, **39 assertions**;
- Architecture complète : **549 / 549**, **43 065 assertions**;
- suite complète : **2 631 / 2 631**, **50 748 assertions**;
- campagne PostgreSQL complète avant l'ajout du test isolé de réversibilité :
  **545 / 545**, **2 318 assertions**;
- test de réversibilité ajouté ensuite et campagne ciblée finale : **PASS**;
- Pint : **PASS**;
- analyse statique : **0 erreur**.

La campagne PostgreSQL complète a nécessité une fenêtre étendue; elle s'est
terminée sans échec. Le dernier changement porte uniquement sur le test
down/up ciblé, exécuté séparément avec succès.

## Verdict enregistré

```text
4.9C Persistence Foundation
→ GO CERTIFIÉ
→ FERMÉ

4.9D Runtime Composition
→ AUTORISÉ
```

Le seul sprint autorisé est `4.9D — Account Status Runtime Composition`.
