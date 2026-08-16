# RC2 Stabilization — Iteration 06

## Périmètre

Cette itération traite exclusivement `Claimed → BeginPublicationReviewV1 → Gateway → SendToReview → UnderReview → HTTP`.

Approve, Projection, Search et Public Listing ne sont pas exécutés.

## Première cause du HTTP 503

La paire Workflow/Aggregate était cohérente en `submitted`. La Gateway atteignait ensuite `SendToReview`, mais cette dépendance était construite avec `RegistryListingPropertyCatalog`.

Pour le Property owner-scoped réel :

- le catalogue Registry historique répondait `unavailable` ;
- `PropertyAuthoringCatalogAdapter` répondait `eligible`.

`SendToReview` rejetait donc la transition sur une indisponibilité artificielle, puis la Gateway réduisait l'exception en `DependencyUnavailable`, exposé en HTTP 503.

## Correction unique

Le Provider de la Gateway définit un binding contextuel limité à `SendToReview`, utilisant l'adaptateur read-only Property Authoring existant.

Aucun modèle Property, aucune Queue, aucun Claim, aucun Submit et aucune règle de transition ne sont modifiés.

## Rejeu

Listing réel : `4f45cbb9-0475-4028-bcd5-caf4a86ca29f`.

Le premier BeginReview retourne HTTP 200. Les états autoritatifs deviennent :

| Modèle | État | Version |
|---|---|---:|
| Workflow | `under_review` | 3 |
| Aggregate Listing | `under_review` | 2 |

Le replay strict de la même requête BeginReview retourne ensuite HTTP 409. Il constitue la première divergence suivante.

Conformément à la règle d'une seule correction, cette divergence de replay n'est pas corrigée ici. Arrêt avant Approve.

## Verdict

**NO GO PROPOSÉ — ITERATION 06**

Le HTTP 503 et UnderReview sont corrigés et démontrés, mais le critère de replay n'est pas satisfait.
