# Authority Model

| Responsabilité | Owner |
|---|---|
| Politique année civile UTC V1 | RealEstateCatalog Application |
| Fourniture de occurredAt stable | Orchestration de commande Property |
| Résolution BusinessYear | BusinessYearAuthorityV1 |
| Validation de l'entier | BusinessYear Value Object |
| Comparaison ConstructionYear | PropertyTypePolicy |

Le Domain consomme la valeur mais ne lit aucune clock. La policy V1 est statique et identifiée par le contrat `BusinessYearAuthorityV1`; aucun registre de versions n'est nécessaire.
