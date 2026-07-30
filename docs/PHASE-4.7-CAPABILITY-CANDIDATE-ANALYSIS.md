# Phase 4.7 — Capability Candidate Analysis

## Méthode

L'audit porte exclusivement sur les modules métier qui ne disposent pas encore d'une chaîne verticale Phase 4 complète. Il examine les contrats Domain et Application, les sources PostgreSQL existantes, les dépendances intermodules et les effets transactionnels. Aucun composant exécutable n'est créé.

## Candidats

| Capacité | Propriétaire | Preuves existantes | Blocage principal |
|---|---|---|---|
| Administrative Action Lifecycle | `AdministrationAudit` | cinq statuts, commandes de création/enregistrement/approbation/rejet, politique four-eyes, événements, registre et repository PostgreSQL propriétaires | `Record` dépend d'un motif et de la décision explicite `requiresFourEyes` |
| Account Status Lifecycle | `IdentityAccess` | Aggregate `Account`, suspension/réactivation, versionnement et événements | la suspension révoque les rôles actifs dans la même décision Aggregate |
| Moderation Case Lifecycle | `ModerationReports` | Aggregate riche, rapports, findings, décisions, politiques et événements | le cycle dépend de preuves, d'acteurs distincts et d'un Listing externe |
| Payment Lifecycle | `MonetizationPayments` | statuts, commandes, événements, preuves de paiement et remboursement | autorisation, capture et remboursement dépendent de systèmes financiers externes |
| Geographic Place Lifecycle | `Geography` | activation, désactivation, fusion, renommage et événements | la fusion produit des effets Search, SEO et redirections historiques |

## Conclusion

`Administrative Action Lifecycle` est retenu. Il combine une valeur transverse élevée, un propriétaire unique, un état métier déjà fermé et une source PostgreSQL propriétaire. Ses ambiguïtés sont localisées et peuvent être levées par des gates contractuels avant le Workflow et avant la persistance Lifecycle.

`Account Status Lifecycle` est différé : séparer le statut de la révocation des rôles créerait une décision partielle. `Moderation Case Lifecycle`, `Payment Lifecycle` et `Geographic Place Lifecycle` restent différés jusqu'à certification de leurs sources ou effets transverses.
