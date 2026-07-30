# Phase 5.2C — Runtime Foundation

## Statut proposé

**GO CERTIFIÉE — FERMÉE.**

## Composition certifiable

`ProfessionalProfileRuntimeV1` expose exclusivement les trois persistences
propriétaires certifiées :

- `ProfessionalPublicProfileStore` ;
- `ProfessionalVerificationStore` ;
- `ProfessionalPublicPortfolioStore`.

Chaque owner possède un provider distinct. Le provider de composition expose une
façade publique unique et une politique locale de disponibilité.

## Availability et diagnostics

La disponibilité est déterministe et fail-closed. Toute absence ou
incompatibilité d’un binding owner produit `MissingBinding` avec seulement un
code de composant fermé et la version `professional-profile-runtime-v1`.

Les diagnostics ne contiennent aucune PII, preuve, donnée d’établissement,
exception, requête SQL, DSN, secret, token ou identifiant de session.

## Frontière F-05

Cette Foundation ne compose pas `Professional Status`. Elle ne lit aucun store,
aucune table et aucun événement F-05. La disponibilité métier complète prévue
par le contrat documentaire reste inactive jusqu’à la certification éventuelle
de `A-5.2C-PROFESSIONAL-STATUS-READ-BOUNDARY-01`.

## Garanties

- bindings lazy et singleton ;
- même connexion PDO Runtime pour les trois stores ;
- aucune transaction ouverte pendant la résolution ou l’inspection ;
- transactions locales conservées dans chaque store certifié ;
- aucune transaction transverse ;
- aucune modification de la migration 061 ou d’une migration antérieure ;
- aucune extension du catalogue Runtime Health historique ;
- aucun HTTP, Event, Delivery, Outbox, consumer ou SQL nouveau.
