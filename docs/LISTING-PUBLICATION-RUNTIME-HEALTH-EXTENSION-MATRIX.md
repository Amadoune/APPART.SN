# Listing Publication Runtime Health Extension Matrix

| Composant | Contrat inspecté | Présence | Compatibilité | Méthode fonctionnelle appelée |
|---|---|---|---|---|
| Listing Publication Workflow | `ListingPublicationWorkflow` | Requise | Instance exacte | Aucune |
| Listing Publication Workflow Store | `ListingPublicationWorkflowStore` | Requise | Implémentation du port | Aucune |

Les deux exigences rejoignent le graphe certifié existant. Leur présence complète produit `Healthy`; une absence ou une incompatibilité reste diagnostiquée par le modèle Runtime Health fermé.
