# Phase 5.2A — Boundary & Dependency Matrix

Légende : `R` lecture par port, `C` commande publique, `E` événement, `X`
interdit.

| Consommateur | IAM F-17 | GEO | Property | Property lifecycle F-02 | Listing | Listing publication F-01 | Media | PUB F-11 |
|---|---:|---:|---:|---:|---:|---:|---:|---:|
| Property Authoring | R | R | C/R | R | X | X | X | X |
| Listing Authoring | R | X | R | R/E | C/R | C/R | R | X |
| Authoring Portfolio | R | X | R | E/R | R | E/R | R | X |

## Dépendances autorisées

- IAM est consommé par ses ports publics Availability et identité de session ;
- Geography valide une référence de Place, sans écriture cross-domain ;
- Listing Authoring lit Property et appelle les commandes publiques Listing ;
- la soumission consomme le lifecycle certifié sans en changer les transitions ;
- Media est une lecture de disponibilité seulement jusqu'à 5.2B ;
- les projections privées consomment résultats ou événements autorisés.

## Interdictions

- SQL, FK ou cascade cross-domain ;
- transaction ACID entre IAM, Property, Listing ou Media ;
- écriture directe dans les tables 001–054 ;
- copie locale du statut Account, Property ou Listing comme autorité ;
- mutation d'un lifecycle via un repository interne contournant son use case ;
- ajout aux catalogues Event V1 gelés ;
- écriture dans Public Listing Projection ;
- controller appelant un autre controller ;
- PII IAM dans un événement authoring.

## Politique de défaillance

Les décisions d'autorisation et de disponibilité sont fail-closed. Une lecture
indisponible, inconnue ou divergente interdit la mutation sans altérer les
owners.
