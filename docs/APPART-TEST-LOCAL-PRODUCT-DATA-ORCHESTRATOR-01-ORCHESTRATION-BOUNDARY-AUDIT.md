# APPART.TEST LOCAL PRODUCT DATA ORCHESTRATOR 01 — Orchestration Boundary Audit

## Conclusion

`NO GO PROPOSÉ`

La commande demandée ne peut pas être matérialisée uniquement par composition des contrats actuels. Deux ruptures empêchent une exécution conforme.

## Rupture 1 — publication

`PropertyListingAuthoringOperations` crée le Listing draft puis délègue Submit au `ListingPublicationOrchestrator`. Celui-ci écrit dans `ListingPublicationWorkflowStore`, distinct du `ListingRegistry` lu par la projection. Aucune composition existante ne reporte BeginReview et ApproveAndPublish dans l'Aggregate du Registry.

Le test E2E compense cette rupture en construisant directement un Aggregate publié ; cette méthode est explicitement interdite.

## Rupture 2 — première génération

La source certifiée de reconstruction exige une génération active. La base locale contient zéro génération active. Le manager sait créer une candidate, mais l'activation exige un manifeste non vide et validé. Le rebuild qui devrait produire ce manifeste est bloqué par `ActiveGenerationMissing`.

```text
aucune génération active
        ↓
source de rebuild = ActiveGenerationMissing
        ↓
aucune projection candidate
        ↓
aucun manifeste non vide valide
        ↓
activation impossible
```

Le test E2E rompt ce cycle par un `INSERT` direct d'une génération active, opération interdite au présent jalon.

## Impact

Une commande créée dans cet état devrait soit écrire directement en SQL, soit forcer un Aggregate publié, soit modifier une surface certifiée de bootstrap de génération. Les trois solutions dépassent l'autorisation.
