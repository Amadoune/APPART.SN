# Phase 4.9B — Account Status Workflow Foundation Certification

## Livrables

- `AccountStatusWorkflow`;
- états `Active` et `Suspended`;
- actions `Suspend` et `Reactivate`;
- décisions `Applied`, `AlreadyInState`, `InvalidContext`;
- `AccountStatusContextV1` et Value Objects immuables;
- matrice exhaustive des transitions;
- tests unitaires et d'architecture.

## Conformité à 4.9A-R1

| Exigence | Preuve |
|---|---|
| transitions métier uniquement | deux transitions, quatre paires exhaustives |
| refus métier fermés | `AlreadyInState`, `InvalidContext` uniquement |
| pureté | aucune dépendance technique ou lecture externe |
| concurrence hors Workflow | `expectedVersion` n'est pas comparée |
| rejeu hors Workflow | aucun résultat d'Inspection ou de rejeu |
| sous-domaines exclus | aucun rôle, session, Credential, Verification ou Consent |
| fondations gelées | `Account`, `AccountRegistry`, Phase 4.8 et migrations inchangés |

## Validations exécutées

- tests ciblés Workflow / Architecture : **13 / 13**, **100 assertions**;
- Architecture complète : **544 / 544**, **42 844 assertions**;
- suite complète : **2 624 / 2 624**, **50 523 assertions**;
- Pint ciblé : **PASS**;
- analyse statique ciblée : **0 erreur**.

Aucune campagne PostgreSQL n'est revendiquée : le sprint ne contient ni
persistance, ni requête, ni migration.

## Verdict enregistré

```text
4.9B Workflow Foundation
→ GO CERTIFIÉ
→ FERMÉ

4.9C-R1 Historical Coexistence Gate
→ GO CERTIFIÉ
→ FERMÉ

4.9C Persistence Foundation
→ GO CERTIFIÉ
→ FERMÉ

4.9D Runtime Composition
→ SUSPENDU AVANT IMPLÉMENTATION
```

La reprise de 4.9D exige un amendement préalable sur la source Runtime
`AccountRegistry`.
