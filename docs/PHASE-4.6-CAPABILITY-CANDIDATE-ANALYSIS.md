# Phase 4.6 — Capability Candidate Analysis

## Inventaire

Les modules encore dépourvus d'une chaîne Lifecycle complète ont été inspectés sans modifier leur code.

| Candidat | Propriétaire | Preuves existantes | Limite structurante |
|---|---|---|---|
| Media Item Lifecycle | `Media` | `MediaStatus`, `MediaItem`, `MediaCollection`, six commandes, six événements, registre et repository PostgreSQL | retrait/archivage d'un média principal exige un remplacement validé par la collection |
| Account Status Lifecycle | `IdentityAccess` | Aggregate `Account`, suspension/réactivation, version, événements et `AccountRegistry` | suspension révoque aussi les rôles actifs ; frontière non réductible au seul statut |
| Moderation Case Lifecycle | `ModerationReports` | Aggregate, cinq use cases, politiques de séparation des responsabilités et événements | décisions fondées sur rapports, findings, acteurs et Listing externe |
| Payment Lifecycle | `MonetizationPayments` | statuts, commandes, événements et preuves de paiement | autorisation/capture/remboursement dépendent d'une source de paiement externe non matérialisée |
| Geographic Place Lifecycle | `Geography` | commandes et événements de création, activation, désactivation, fusion et renommage | effets transverses sur Search, SEO et Historical Redirect |

## Conclusion

`Media Item Lifecycle` est le seul candidat combinant un propriétaire unique, un état fermé déjà explicite, des opérations Domain existantes et une fondation PostgreSQL propriétaire. Les invariants de collection ne sont pas transférés au futur workflow : ils constituent un gate contractuel préalable à l'orchestration.
