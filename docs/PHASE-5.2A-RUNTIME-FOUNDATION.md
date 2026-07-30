# Phase 5.2A — Runtime Foundation

## Statut

**GO CERTIFIÉ — FERMÉ.**

## Composition

La fondation Runtime compose les quatre autorités certifiées sans modifier leur
persistance :

| Autorité | Reader/Writer Runtime | Provider propriétaire |
|---|---|---|
| PropertyAuthoring | `PropertyAuthoringStore` | `PropertyAuthoringRuntimeServiceProvider` |
| ListingAuthoringDraft | `ListingDraftStore` | `ListingAuthoringRuntimeServiceProvider` |
| ListingOwnership | `ListingOwnershipStore` | `ListingAuthoringRuntimeServiceProvider` |
| AuthoringPortfolio | `AuthoringPortfolioStore` | `ListingAuthoringRuntimeServiceProvider` |

`PropertyListingAuthoringRuntimeV1` constitue la composition publique. Il
expose les quatre ports propriétaires sans ajouter de logique métier, de
transaction transverse ou de persistance.

## Availability

La politique `PropertyListingAuthoringRuntimeAvailabilityPolicy` est
déterministe et fail-closed. Elle exige des bindings compatibles pour les
quatre autorités et pour le contrat public `AccountAvailabilityInspector`.

Elle retourne exclusivement :

- `Ready` ;
- `MissingBinding(code)` ;
- `DependencyUnavailable(code)` ;
- `IncompatibleVersion(code)`.

La politique ne réinterprète aucun état IAM et n’étend pas les finalités
certifiées d’Account Availability.

## Diagnostics

Les diagnostics sont séparés de Runtime Health F-14. Ils vérifient uniquement
la présence et la compatibilité de type des bindings, sans requête métier ni
ouverture de transaction.

## Frontières

- bindings Laravel exclusivement dans les providers ;
- Application indépendante de Laravel, PDO et Infrastructure ;
- singletons lazy partageant la connexion PostgreSQL Runtime existante ;
- aucun HTTP, Event, Delivery, Outbox, Controller, Route ou Middleware ;
- aucune migration ;
- catalogue Runtime Health historique maintenu à 58 capacités.
