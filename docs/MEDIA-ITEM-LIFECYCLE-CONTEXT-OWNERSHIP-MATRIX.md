# Media Item Lifecycle Context Ownership Matrix

| Information ou décision | Propriétaire | Futur orchestrateur |
|---|---|---|
| état Lifecycle et transition de statut | `MediaItemLifecycleWorkflow` | délègue |
| statut principal du média | `MediaCollection` | ne lit pas, ne déduit pas |
| sélection et validation du remplaçant | `MediaCollection` | transporte uniquement |
| version de collection | appelant issu de `MediaCollection` | compare ou persiste selon contrat futur |
| version attendue du journal Lifecycle | appelant | contrôle explicitement |
| acteur et `occurredAt` | appelant | transmet sans génération |
| checksum contextuel | contrat V1 | recalcule mécaniquement pour l'intégrité uniquement |

Le checksum ne constitue jamais une décision métier.
