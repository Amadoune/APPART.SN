# Phase 5.2A — HTTP Foundation

## Statut

**GO CERTIFIÉ — FERMÉ.**

## Frontière exposée

La fondation expose onze endpoints privés sous `/api/authoring` :

- initiation, lecture et mise à jour de PropertyAuthoring ;
- demande de création Listing ;
- lecture et mise à jour du draft ;
- évaluation de complétude ;
- ajout et retrait de délégation ;
- demande de soumission ;
- lecture du portfolio.

Toutes les routes utilisent le contrôleur fermé
`PropertyListingAuthoringHttpController` et le contrat Application
`PropertyListingAuthoringHttpRuntime`.

## Sécurité

- session IAM certifiée obligatoire et fail-closed ;
- AccountId exclusivement auto-scopé depuis la session ;
- aucun `accountId` accepté dans le payload public ;
- `Idempotency-Key` UUID obligatoire pour chaque mutation ;
- refus systématique des champs inconnus ;
- identifiants de route validés comme UUID ;
- permissions fermées `VIEW`, `EDIT`, `SUBMIT` ;
- lecture et écriture refusées sous une réponse homogène `404` ;
- rate limiting indexé par une empreinte HMAC sans PII ;
- réponses privées `Cache-Control: no-store`, `Pragma: no-cache`,
  `X-Content-Type-Options: nosniff` et `Referrer-Policy: no-referrer`.

## Intégration Runtime

L’adaptateur consomme exclusivement `PropertyListingAuthoringRuntimeV1`.
Il ne dépend ni de PDO, ni d’Infrastructure, ni d’un repository concret.

Les opérations Property, draft, ownership et portfolio utilisent les
readers/writers propriétaires certifiés. La création complète de Listing et le
handoff de soumission restent fail-closed tant qu’un point d’entrée opérationnel
correspondant n’est pas exposé par la composition Runtime certifiée. Aucun
contournement de F-01 n’est introduit.

## Frontières préservées

Aucune migration, transaction transverse, modification Event V1, Delivery,
Outbox ou Runtime Health. F-01, F-02, F-11, F-14, F-15, F-17 et F-18 restent
inchangées.
