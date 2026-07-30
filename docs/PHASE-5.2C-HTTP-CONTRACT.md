# Phase 5.2C — HTTP Contract documentaire

## Statut

Contrat de frontière uniquement. Aucun controller, route, middleware, request,
provider ou code HTTP n’est créé. L’activation HTTP est bloquée par la réserve
F-05.

## Endpoints privés candidats

| Méthode | URI candidate | Opération |
|---|---|---|
| POST | `/api/v1/professional-profile` | créer le profil |
| PATCH | `/api/v1/professional-profile` | réviser le profil |
| POST | `/api/v1/professional-profile/publish` | publier |
| POST | `/api/v1/professional-profile/hide` | masquer |
| POST | `/api/v1/professional-verification/requests` | demander une vérification |
| GET | `/api/v1/professional-verification` | lire le résumé privé |
| GET | `/api/v1/professional-establishments` | lister les établissements |
| GET | `/api/v1/professional-mandates` | lister les mandats |

Les décisions Grant/Reject/Revoke relèvent d’une future autorité administrative
authentifiée et ne sont pas exposées au professionnel lui-même par défaut.

## Endpoints publics candidats

| Méthode | URI candidate | Résultat |
|---|---|---|
| GET | `/api/v1/professionals/{professionalId}` | profil public disponible |
| GET | `/api/v1/professionals/{professionalId}/portfolio` | portefeuille public |

Une indisponibilité Account/Status/Profile est présentée uniformément sans
révéler la cause interne.

## Validation et autorisation

- session IAM obligatoire pour toute mutation ;
- `ProfessionalId` privé auto-scopé depuis le mandat de l’Account ;
- champs inconnus refusés ;
- `Idempotency-Key` UUID obligatoire pour toute mutation ;
- expected version explicite ;
- CSRF pour interface web sessionnée ;
- rate limiting par empreinte HMAC sans PII ;
- délégation professionnelle vérifiée séparément de F-19 ;
- aucune autorisation dérivée d’un champ client.

## Réponses

- JSON fermé et versionné ;
- `Cache-Control: no-store` pour privé et vérification ;
- politique cache publique explicite pour profil visible ;
- `X-Content-Type-Options: nosniff` ;
- aucune exception, SQL ou diagnostic interne ;
- erreurs publiques homogènes : invalid_request, unauthorized, forbidden,
  unavailable, conflict, not_found.

## Confidentialité

Jamais exposés :

- email/téléphone IAM non publiés ;
- credentials, session, token ou claim ;
- document ou référence de stockage de preuve ;
- reviewer, motif libre ou diagnostic fournisseur ;
- registration number lorsque la politique publique ne l’autorise pas ;
- état F-05 brut si la politique publique exige seulement disponibilité.
