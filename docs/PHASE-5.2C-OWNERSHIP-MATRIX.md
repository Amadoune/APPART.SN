# Phase 5.2C — Ownership Matrix

## Owners uniques

| Capacité | Aggregate Owner | Event Owner | Projection Owner | HTTP Owner |
|---|---|---|---|---|
| identité légale, établissements, mandats | Professional historique | Professionals.Core pour événements historiques | aucun | Professional Profile private adapter futur |
| présentation publique | ProfessionalPublicProfile | Professionals.Profile | Professionals.PublicProfile | Professional Profile adapter futur |
| vérification | ProfessionalVerification | Professionals.Verification | Professionals.VerificationBadge | Professional Verification adapter futur |
| portefeuille public | aucun Aggregate | aucun Event Owner propre par défaut | Professionals.PublicPortfolio | Professional Public adapter futur |
| disponibilité | aucun Aggregate | aucun | aucune persistence | Professional Profile adapter futur |

## Propriétés interdites

| Donnée/décision | Owner externe conservé |
|---|---|
| Account, session, credentials, UserProfile IAM | F-17/F-18 |
| Active/Suspended professionnel | F-05 |
| Listing et publication | F-01 |
| Property/Listings Authoring, ownership et délégations | F-19/F-20 |
| Media Item et Media Ingestion | F-06, F-21/F-22 |
| Runtime Health historique | F-14 |
| delivery générique gelé | F-15 |

## Règles

- un Event Type possède exactement un Event Owner ;
- une table future possède exactement un persistence owner ;
- la projection Portfolio n’écrit jamais dans ses sources ;
- l’HTTP Owner adapte les contrats et ne décide aucun invariant ;
- Availability compose des décisions et n’en devient jamais owner ;
- les diagnostics internes ne changent pas l’ownership métier.

## Délégations

Le mandat professionnel prouve qu’un Account représente un établissement. Il
ne prouve pas qu’il peut modifier un Listing. Une opération Authoring exige en
plus une autorisation F-19 obtenue par contrat public. L’intersection des deux
droits est évaluée, jamais persistée comme nouvelle autorité.

## Réserve

F-05 reste inaccessible en lecture publique tant que
`A-5.2C-PROFESSIONAL-STATUS-READ-BOUNDARY-01` n’est pas certifié. Aucun owner
5.2C ne peut absorber temporairement cette responsabilité.
