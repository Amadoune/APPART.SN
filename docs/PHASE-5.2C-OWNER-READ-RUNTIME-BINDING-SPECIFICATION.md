# Phase 5.2C — Owner Read Runtime Binding Specification

## Graphe cible

```text
ProfessionalStatusPublicRead owner provider
  ProfessionalPublicStatusReaderV1
    → implementation F-05
      → ProfessionalStatusWorkflowStore interne

ProfessionalMandateResolution owner provider
  ProfessionalMandateResolverV1
    → implementation Professional Core
      → source owner canonique Account/mandat/Professional
```

## Règles

- providers distincts et owner-scoped ;
- singleton et lazy ;
- aucun provider générique ;
- aucune résolution au bootstrap ;
- aucune transaction ouverte ;
- aucun fallback ou Null Object ;
- aucune dépendance HTTP ;
- Runtime Health inchangé à 58 capacités.

## État actuel

| Binding | Contrat | Implémentation | Source owner | État |
|---|---|---|---|---|
| F-05 public status | certifié | absente mais faisable | store F-05 existant | non créé |
| Professional mandate | certifié | impossible actuellement | absente | bloquant |

Créer un alias Laravel pour `ProfessionalMandateResolverV1` sans implémentation
réelle rendrait le container non résolvable. Le binder vers un fallback
fail-closed est interdit.

## Décision

Aucun binding n’est créé tant que le graphe complet ne peut pas être résolu avec
des implémentations owner certifiables.
