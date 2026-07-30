# Phase 4.7D — Runtime Orchestration Analysis

L'orchestrateur coordonne exclusivement les fondations certifiées :

```text
chemin nominal
read → expectedVersion → Workflow → append contextuel

chemin de rejeu
read → expectedVersion + 1 → inspection exacte → replay policy
```

Le chemin de rejeu ne rappelle jamais le Workflow. Le contexte, le Decision Context V1 et la transition ne sont jamais reconstruits.

La composition Laravel ajoute les implémentations contextuelles, l'inspecteur, la politique et l'orchestrateur comme singletons paresseux. Seul l'orchestrateur devient une capacité Runtime Health publique.
