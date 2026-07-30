# Phase 5.2B — Runtime & HTTP Contract

## Runtime

`MediaIngestionRuntimeV1` compose exclusivement les quatre ports owners. Il
retourne des résultats fermés et ne révèle aucun adapter. Availability est
fail-closed sur IAM, scope F-19, quota, stockage, scan et processing.

## HTTP privé candidat

| Opération | Méthode |
|---|---|
| réserver | POST |
| finaliser | POST |
| abandonner | POST |
| statut upload | GET |
| statut asset | GET |
| rattacher asset Ready | POST |

Les routes exactes restent réservées au jalon HTTP. Chaque mutation exige
session IAM, auto-scope, CSRF navigateur et `Idempotency-Key` UUID.

## Réponses

- erreurs d’existence/autorisation homogénéisées ;
- `Cache-Control: no-store`, `X-Content-Type-Options: nosniff` ;
- aucune PII dans rate-limit keys ou logs ;
- URL d’upload opaque, courte, liée à méthode, audience, taille et expiration ;
- aucune réponse ne contient object key, chemin, token de scan ou diagnostic
  interne.
