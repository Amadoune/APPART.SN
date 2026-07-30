# Phase 5.2A — Persistence Strategy

## Principes

- migrations exclusivement additives et postérieures à 054 ;
- schémas propriétaires `real_estate_catalog_authoring` et
  `listing_authoring`, ou namespaces physiques équivalents certifiés ;
- aucune modification des migrations existantes ;
- aucune FK vers IAM, Geography, Media ou un autre owner ;
- FK locale autorisée uniquement entre tables du même owner ;
- optimistic locking sur toutes les racines mutables ;
- idempotence par `(owner, operation, intent_id)` et checksum canonique.

## Stores envisagés

| Store | Autorité | Clé | Concurrence |
|---|---|---|---|
| Property ownership | Property Authoring | PropertyId | unicité PropertyId + version |
| Listing draft | Listing Authoring | ListingId | expected version |
| Listing ownership | Listing Ownership | ListingId | unicité titulaire |
| Delegations | Listing Ownership | ListingId + AccountId + permission | unicité atomique |
| Draft revisions | Listing Authoring | ListingId + sequence | append-only |
| Operation intents | owner local | owner + operation + intentId | checksum divergent |
| Portfolio checkpoint | Projection | AccountId + generation | swap/rebuild |

## Atomicité

Une transaction locale peut regrouper mutation, révision et intent d'un même
owner. La création coordonnée Property/Listing utilise des étapes rejouables ;
elle ne crée pas de transaction PostgreSQL cross-owner.

## Rétention

Les brouillons abandonnés sont conservés selon une politique versionnée à
définir dans Contracts. Les journaux d'idempotence et révisions nécessaires à
l'audit ne sont jamais purgés implicitement. Aucun mécanisme d'erasure IAM
n'est introduit.
