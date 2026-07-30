# Phase 5.2C — Runtime Contract documentaire

## Composition publique candidate

`ProfessionalProfileRuntimeV1` composera conceptuellement :

- Professional Core reader/writer ;
- ProfessionalPublicProfile reader/writer ;
- ProfessionalVerification reader/writer ;
- ProfessionalPublicPortfolio reader ;
- ProfessionalAvailabilityPolicy.

Ce nom ne constitue pas une interface PHP autorisée.

## Availability

La politique est pure, sans table ni écriture :

```text
Account disponible F-17
AND Professional actif F-05
AND PublicProfile Visible
AND Verification valide lorsque requise
= Available
```

Résultats fermés :

- `Available` ;
- `AccountUnavailable` ;
- `ProfessionalStatusUnavailable` ;
- `ProfileHidden` ;
- `VerificationRequired` ;
- `DependencyUnavailable` ;
- `Corrupted`.

Toute source absente, non certifiée, indisponible ou corrompue produit un résultat
fail-closed.

## Gate F-05

La composition de statut est interdite tant que
`A-5.2C-PROFESSIONAL-STATUS-READ-BOUNDARY-01` n’est pas ouvert puis certifié.
Sont expressément interdits :

- lecture du `ProfessionalStatusWorkflowStore` ;
- requête SQL sur les tables 027–030 ;
- invocation d’un orchestrateur de commande pour simuler une lecture ;
- reconstruction du statut à partir d’événements ou projections non normatives.

## Bindings futurs

- owner-scoped ;
- lazy et singleton ;
- aucun side effect à la résolution ;
- connexion PostgreSQL partagée sans transaction ouverte ;
- Application indépendante de Laravel, PDO et Infrastructure ;
- providers distincts Core/Profile/Verification/Portfolio ;
- aucune extension implicite du catalogue Runtime Health.

## Diagnostics

Diagnostic interne fermé :

- component code ;
- availability outcome ;
- dependency code ;
- policy version.

Sont interdits : PII, registration number complet, preuve, document, SQL,
exception, DSN, secret, token et identifiant de session.

## Frontières transactionnelles

- transaction locale par owner ;
- savepoint seulement pour participation locale englobante certifiée ;
- aucune transaction Profile + Verification + IAM + Listing ;
- handoffs cross-domain après commit, par contrat public ou événement certifié ;
- rollback intégral de l’owner ayant commencé l’opération.
