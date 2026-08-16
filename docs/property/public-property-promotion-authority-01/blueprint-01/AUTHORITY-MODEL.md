# Authority Model

| Responsabilité | Owner V1 | Autorisé | Interdit |
|---|---|---|---|
| Orchestration de promotion | RealEstateCatalog Application, composant dédié Public Property Promotion | Lire le snapshot Authoring owner-scoped, porter commandId/checksum/ledger, appeler `RegisterProperty` | Valider ou construire directement l'Aggregate |
| État préparatoire | Property Authoring | Fournir le snapshot à version attendue | Devenir Aggregate Domain |
| Création Aggregate | RealEstateCatalog Domain via `RegisterProperty` | Valider place et invariants, produire événements, persister via Registry | Accepter des valeurs fictives ou des faits Projection |
| Identité de commande et replay | Public Property Promotion | Détecter replay identique/divergent | Réutiliser un ledger Listing ou Projection |
| Listing Submit | Listing Authoring/Lifecycle | Exiger un succès fermé de promotion avant `Submitted` | Masquer un échec Property |
| Projection | Public Projection | Lire l'Aggregate enregistré | Lire Authoring ou créer Property |

L'owner applicatif unique est **Public Property Promotion dans RealEstateCatalog Application**. Il possède l'orchestration, pas les invariants Domain. L'owner de session reste IAM ; `ownerAccountId` est dérivé de la session et comparé à l'état Authoring, jamais accepté comme fait métier client.
