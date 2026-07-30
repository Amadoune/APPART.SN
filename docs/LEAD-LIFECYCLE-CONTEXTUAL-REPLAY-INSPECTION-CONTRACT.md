# Lead Lifecycle Contextual Replay Inspection Contract

## Objet

`LeadLifecycleContextualReplayInspector` expose le dernier append contextuel sans reconstruire une transition. Le snapshot contient l'identité Lead, la version, la transition exacte, l'acteur, l'instant UTC explicite et le checksum contextuel.

## Résultats fermés

| Statut | Snapshot | Signification |
|---|---:|---|
| `Found` | obligatoire | dernier append restauré et intègre |
| `Missing` | interdit | aucun append contextuel disponible |
| `Corrupted` | interdit | données présentes mais non restaurables ou incohérentes |

Le port est additif et distinct de `LeadLifecycleContextualTransitionStore`. Aucune implémentation, migration, composition Runtime ou décision métier n'appartient à 4.4D-R3.

La future orchestration n'utilisera l'inspection que lorsque la version courante vaut `expectedVersion + 1`. L'égalité complète produit `AlreadyApplied`; seule une divergence de contexte produit `ContextDivergence`; toute autre divergence reste un conflit explicite.
