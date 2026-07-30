# Place Lifecycle Runtime Binding Matrix

| Contrat / composant | Enregistrement | Cible | Cycle |
|---|---|---|---|
| `PlaceLifecycleWorkflow` | singleton paresseux | lui-même | partagé |
| `PlaceLifecycleWorkflowMapper` | singleton paresseux | lui-même | partagé |
| `PostgreSqlPlaceLifecycleWorkflowStore` | singleton paresseux | lui-même | partagé |
| `PlaceLifecycleWorkflowStore` | alias unique | store PostgreSQL | même instance |

Aucun composant n'est résolu pendant `register()`. La connexion PDO n'est
résolue que lors de la première résolution du store.

## Runtime Health

Deux capacités additives sont enregistrées :

- `place_lifecycle_workflow`;
- `place_lifecycle_workflow_store`.

Les 50 capacités préexistantes restent inchangées. Le catalogue certifiable
contient désormais 52 capacités.
